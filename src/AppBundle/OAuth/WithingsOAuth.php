<?php
namespace AppBundle\OAuth;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\OAuth1\Service\AbstractService;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\OAuth1\Signature\SignatureInterface;
use OAuth\OAuth1\Token\StdOAuth1Token;

/**
 * Class WithingsOAuth
 * @package AppBundle\OAuth2
 */
class WithingsOAuth extends AbstractService
{
    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        SignatureInterface $signature,
        UriInterface $baseApiUri = null
    )
    {
        parent::__construct($credentials, $httpClient, $storage, $signature, $baseApiUri);
        $this->baseApiUri = new Uri('http://wbsapi.withings.net/');
    }

    /**
     * Parses the request token response and returns a TokenInterface.
     * This is only needed to verify the `oauth_callback_confirmed` parameter. The actual
     * parsing logic is contained in the access token parser.
     *
     *
     * @param string $responseBody
     *
     * @return \OAuth\OAuth1\Token\TokenInterface
     *
     * @throws TokenResponseException
     */
    protected function parseRequestTokenResponse($responseBody)
    {
w        parse_str($responseBody, $data);

        print_r($data);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (!isset($data['oauth_token']) || !isset($data['oauth_token_secret'])) {
            throw new TokenResponseException('Error in retrieving token.');
        }

        return $this->parseAccessTokenResponse($responseBody);
    }

    /**
     * Parses the access token response and returns a TokenInterface.
     *
     *
     * @param string $responseBody
     *
     * @return \OAuth\OAuth1\Token\TokenInterface
     *
     * @throws TokenResponseException
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
        return new Uri('https://oauth.withings.com/account/access_token');
    }

    /**
     * @return UriInterface
     */
    public function getRequestTokenEndpoint()
    {
        return new Uri('https://oauth.withings.com/account/request_token');
    }

    /**
     * Returns the authorization API endpoint.
     *
     * @return UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://oauth.withings.com/account/authorize');
    }

    /**
     * Sends an authenticated API request to the path provided.
     * If the path provided is not an absolute URI, the base API Uri (must be passed into constructor) will be used.
     *
     * @param string|UriInterface $path
     * @param string              $method       HTTP method
     * @param array               $body         Request body if applicable (key/value pairs)
     * @param array               $extraHeaders Extra headers if applicable.
     *                                          These will override service-specific any defaults.
     *
     * @return string
     */
    public function request($path, $method = 'GET', $body = null, array $extraHeaders = array())
    {
        $uri = $this->determineRequestUriFromPath($path, $this->baseApiUri);

        /** @var $token StdOAuth1Token */
        $token = $this->storage->retrieveAccessToken($this->service());

        $realPath = $this->baseApiUri . $path . $this->createUri($method, $uri, $token, $body);

        $uri = new Uri($realPath);

        return $this->httpClient->retrieveResponse($uri, $body, [], $method);
    }

    /**
     * Builds the authorization uri parameters for an authenticated API request
     *
     * @param string         $method
     * @param UriInterface   $uri        The uri the request is headed
     * @param TokenInterface $token
     * @param array          $bodyParams Request body if applicable (key/value pairs)
     *
     * @return string
     */
    protected function createUri(
        $method,
        UriInterface $uri,
        TokenInterface $token,
        $bodyParams = null
    ) {
        $this->signature->setTokenSecret($token->getAccessTokenSecret());
        $parameters = $this->getBasicAuthorizationHeaderInfo();
        if (isset($parameters['oauth_callback'])) {
            unset($parameters['oauth_callback']);
        }

        $parameters = array_merge($parameters, array('oauth_token' => $token->getAccessToken()));
        $parameters = (is_array($bodyParams)) ? array_merge($parameters, $bodyParams) : $parameters;
        $parameters['oauth_signature'] = $this->signature->getSignature($uri, $parameters, $method);

        $authorizationHeader = '';
        $delimiter = '&';

        foreach ($parameters as $key => $value) {
            $authorizationHeader .= $delimiter . rawurlencode($key) . '=' . rawurlencode($value) . '';
            $delimiter = '&';
        }

        return $authorizationHeader;
    }
}
