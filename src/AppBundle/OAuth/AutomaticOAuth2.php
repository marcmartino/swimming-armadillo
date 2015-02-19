<?php
namespace AppBundle\OAuth;

use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\OAuth2\Token\StdOAuth2Token;

/**
 * Class AutomaticOAuth2
 * @package AppBundle\OAuth
 */
class AutomaticOAuth2 extends AbstractService
{
    const
        SCOPE_PUBLIC = 'scope:public',
        SCOPE_USER_PROFILE = 'scope:user:profile',
        SCOPE_USER_FOLLOW = 'scope:user:follow',
        SCOPE_LOCATION = 'scope:location',
        SCOPE_CURRENT_LOCATION = 'scope:current_location',
        SCOPE_VEHICLE_PROFILE = 'scope:vehicle:profile',
        SCOPE_VEHICLE_EVENTS = 'scope:vehicle:events',
        SCOPE_VEHICLE_VIN = 'scope:vehicle:vin',
        SCOPE_TRIP = 'scope:trip',
        SCOPE_BEHAVIOR = 'scope:behavior';

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
        $data = json_decode($responseBody, true);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }

        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);
        $token->setEndOfLife(StdOAuth2Token::EOL_NEVER_EXPIRES);
        unset($data['access_token']);

        $token->setExtraParams($data);

        return $token;
    }

    /**
     * Returns the authorization API endpoint.
     *
     * @return UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://accounts.automatic.com/oauth/authorize');
    }

    /**
     * Returns the access token API endpoint.
     *
     * @return UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://accounts.automatic.com/oauth/access_token');
    }
}