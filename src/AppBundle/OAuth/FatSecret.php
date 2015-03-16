<?php

namespace AppBundle\OAuth;


use DateTime;
use OAuth\Common\Http\Uri\Uri;
use OAuth\OAuth1\Token\TokenInterface;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\OAuth1\Service\AbstractService;
use OAuth\Common\Http\Exception\TokenResponseException;

class FatSecret extends AbstractService {

    /**
     * Parses the request token response and returns a TokenInterface.
     * This is only needed to verify the `oauth_callback_confirmed` parameter. The actual
     * parsing logic is contained in the access token parser.
     *
     *
     * @param string $responseBody
     *
     * @return TokenInterface
     *
     * @throws TokenResponseException
     */
    protected function parseRequestTokenResponse($responseBody)
    {
        // TODO: Implement parseRequestTokenResponse() method.
    }

    /**
     * Parses the access token response and returns a TokenInterface.
     *
     *
     * @param string $responseBody
     *
     * @return TokenInterface
     *
     * @throws TokenResponseException
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        // TODO: Implement parseAccessTokenResponse() method.
    }

    /**
     * Returns the access token API endpoint.
     *
     * @return UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return  new Uri('http://www.fatsecret.com/oauth/access_token');
    }

    /**
     * @return UriInterface
     */
    public function getRequestTokenEndpoint()
    {
        return  new Uri('http://www.fatsecret.com/oauth/request_token');
    }

    /**
     * Returns the authorization API endpoint.
     *
     * @return UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return  new Uri('http://www.fatsecret.com/oauth/authorize');
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
}