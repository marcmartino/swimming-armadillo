<?php
namespace AppBundle\OAuth;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\AbstractService;

/**
 * Class AutomaticOAuth2
 * @package AppBundle\OAuth
 */
class AutomaticOAuth2 extends AbstractService
{

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
     * Returns the authorization API endpoint.
     *
     * @return UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        // TODO: Implement getAuthorizationEndpoint() method.
    }

    /**
     * Returns the access token API endpoint.
     *
     * @return UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        // TODO: Implement getAccessTokenEndpoint() method.
    }
}