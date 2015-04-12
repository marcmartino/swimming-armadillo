<?php

namespace AppBundle\OAuth;


use DateTime;
use OAuth\Common\Http\Uri\Uri;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\OAuth1\Token\TokenInterface;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\OAuth1\Service\AbstractService;
use OAuth\Common\Http\Exception\TokenResponseException;

class FatSecret extends AbstractService
{

    /**
     * {@inheritdoc}
     */
    protected function parseRequestTokenResponse($responseBody)
    {
        return $this->parseAccessTokenResponse($responseBody);
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        parse_str($responseBody, $data);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }

        $token = new StdOAuth1Token();

        $token->setRequestToken($data['oauth_token']);
        $token->setRequestTokenSecret($data['oauth_token_secret']);
        $token->setAccessToken($data['oauth_token']);
        $token->setAccessTokenSecret($data['oauth_token_secret']);

        $token->setEndOfLife(StdOAuth1Token::EOL_NEVER_EXPIRES);
        unset($data['oauth_token'], $data['oauth_token_secret']);
        $token->setExtraParams($data);

        return $token;
    }

    /**
     * Returns the access token API endpoint.
     *
     * @return UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('http://www.fatsecret.com/oauth/access_token');
    }

    /**
     * @return UriInterface
     */
    public function getRequestTokenEndpoint()
    {
        return new Uri('http://www.fatsecret.com/oauth/request_token');
    }

    /**
     * Returns the authorization API endpoint.
     *
     * @return UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('http://www.fatsecret.com/oauth/authorize');
    }

    public function requestRequestToken()
    {
        $signatureParams = array(
            'oauth_consumer_key' => $this->credentials->getConsumerId(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (new DateTime)->getTimestamp(),
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_callback' => $this->credentials->getCallbackUrl()
        );

        $bodyParams = array_merge($signatureParams, [
            'oauth_signature' => $this->signature->getSignature(
                $this->getRequestTokenEndpoint(),
                $signatureParams,
                'POST'
            )
        ]);

        $authorizationHeader = array('Authorization' => $this->buildAuthorizationHeaderForTokenRequest($signatureParams));
        $headers = array_merge($authorizationHeader, $this->getExtraOAuthHeaders());


        $responseBody = $this->httpClient->retrieveResponse($this->getRequestTokenEndpoint(), $bodyParams, $headers);

        $token = $this->parseRequestTokenResponse($responseBody);
        $this->storage->storeAccessToken($this->service(), $token);

        return $token;
    }

    /**
     * {@inheritDoc}
     */
    public function requestAccessToken($token, $verifier, $tokenSecret = null)
    {
        if (is_null($tokenSecret)) {
            $storedRequestToken = $this->storage->retrieveAccessToken($this->service());
            $tokenSecret = $storedRequestToken->getRequestTokenSecret();
        }
        $this->signature->setTokenSecret($tokenSecret);

        $bodyParams = array(
            'oauth_verifier' => $verifier,
        );

        $signatureParams = array(
            'oauth_consumer_key' => $this->credentials->getConsumerId(),
            'oauth_token' => $this->storage->retrieveAccessToken($this->service())->getRequestToken(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (new DateTime)->getTimestamp(),
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_verifier' => $_GET['oauth_verifier']
        );

        /**
         * oauth_consumer_key
        Your consumer key (you can obtain one by registering here)
        oauth_token
        The Request Token obtained in Obtaining a Request Token which has been authorized
        oauth_signature_method
        We only support "HMAC-SHA1"
        oauth_timestamp
        The date and time, expressed in the number of seconds since January 1, 1970 00:00:00 GMT. The timestamp value must be a positive integer and must be equal or greater than the timestamp used in previous requests
        oauth_nonce
        A randomly generated string for a request that can be combined with the timestamp to produce a unique value
        oauth_version
        Must be "1.0"
        oauth_verifier
         */

        $bodyParams = array_merge($signatureParams, [
            'oauth_signature' => $this->signature->getSignature(
                $this->getRequestTokenEndpoint(),
                $signatureParams,
                'POST'
            )
        ]);

        $authorizationHeader = array(
            'Authorization' => $this->buildAuthorizationHeaderForAPIRequest(
                'POST',
                $this->getAccessTokenEndpoint(),
                $this->storage->retrieveAccessToken($this->service()),
                $bodyParams
            )
        );

        $headers = array_merge($authorizationHeader, $this->getExtraOAuthHeaders());

        $responseBody = $this->httpClient->retrieveResponse($this->getAccessTokenEndpoint(), $bodyParams, $headers);

        $token = $this->parseAccessTokenResponse($responseBody);
        $this->storage->storeAccessToken($this->service(), $token);

        return $token;
    }
}