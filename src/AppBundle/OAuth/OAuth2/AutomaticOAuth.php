<?php
namespace AppBundle\OAuth\OAuth2;

use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\Container;
use OAuth\Common\Http\Exception\TokenResponseException;

class AutomaticOAuth extends AbstractService {

    /**
     * @var Container
     */
    private $container;

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        Container $container
    ) {
        parent::__construct($credentials, $httpClient, $storage);

        $this->credentials = $credentials;
        $this->httpClient = $httpClient;
        $this->storage = $storage;
        $this->container = $container;

        $this->baseApiUri = new Uri($container->getParameter('automatic_base_uri'));
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
     * Returns the authorization API endpoint.
     *
     * @return UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri($this->container->getParameter('automatic_authorization_uri'));
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