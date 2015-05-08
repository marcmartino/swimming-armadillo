<?php
namespace AppBundle\OAuth;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Storage\TokenStorageInterface;
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

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null,
        $stateParameterInAutUrl = false
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri, $stateParameterInAutUrl);
        $this->baseApiUri = new Uri('https://api.automatic.com/v1/');
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
     * https://accounts.automatic.com/oauth/authorize/?client_id=553f2bc0a03cd495b70e&response_type=code&scope=scope%3Apublic+scope%3Auser%3Aprofile+scope%3Alocation+scope%3Avehicle%3Aprofile+scope%3Avehicle%3Aevents+scope%3Atrip+scope%3Abehavior
     * https://accounts.automatic.com/oauth/authorize/?client_id=553f2bc0a03cd495b70e&response_type=code&scope=scope:vehicle:profile%20scope:behavior%20scope:location%20scope:vehicle:events%20scope:trip%20scope:public%20scope:user:profile
     */

    /**
     * Returns the authorization API endpoint.
     *
     * @return UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://accounts.automatic.com/oauth/authorize/');
    }

    /**
     * Returns the access token API endpoint.
     *
     * @return UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://accounts.automatic.com/oauth/access_token/');
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUri(array $additionalParameters = array())
    {
        $parameters = array_merge(
            $additionalParameters,
            array(
                'client_id'     => $this->credentials->getConsumerId(),
                'response_type' => 'code',
            )
        );

        $parameters['scope'] = implode(' ', $this->scopes);

        if ($this->needsStateParameterInAuthUrl()) {
            if (!isset($parameters['state'])) {
                $parameters['state'] = $this->generateAuthorizationState();
            }
            $this->storeAuthorizationState($parameters['state']);
        }

        // Build the url
        $url = clone $this->getAuthorizationEndpoint();
        foreach ($parameters as $key => $val) {
            $url->addToQuery($key, $val);
        }

        return $url;
    }
}