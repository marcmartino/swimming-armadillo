<?php
/**
 * Created by PhpStorm.
 * User: Nate
 * Date: 3/15/15
 * Time: 4:06 PM
 */

namespace AppBundle\ApiAdapter\Provider;


use AppBundle\ApiAdapter\AbstractApiAdapter;
use AppBundle\ApiAdapter\ApiAdapterInterface;
use AppBundle\OAuth\FatSecret;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Client\CurlClient;
use OAuth\ServiceFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FatsecretApiAdapter extends AbstractApiAdapter implements ApiAdapterInterface {

    /**
     * @var \OAuth\Common\Service\ServiceInterface
     */
    protected $fatSecretService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->storage = $this->container->get('token_storage_session');;

        $this->fatSecretService = $this->createService();
    }

    /**
     * @return \OAuth\Common\Service\ServiceInterface
     * @throws \OAuth\Common\Exception\Exception
     */
    public function createService()
    {
        $credentials = new Credentials(
            'de6e50d0f7304cf2b877ac62097f0ca2',
            'e1edbe8d2a45424581d0bd3e3a669e93',
            'http://hdlbit.com/fatsecret/callback'
        );

        $serviceFactory = new ServiceFactory();
        $serviceFactory->setHttpClient((new CurlClient));
        $serviceFactory->registerService('FatSecret', 'AppBundle\\OAuth\\FatSecret');

        /** @var FatSecret $withingsService */
        return $fatSecretService = $serviceFactory->createService('FatSecret', $credentials, $this->storage);
    }

    public function getService()
    {
        return $this->fatSecretService;
    }

    /**
     * Return URI for oauth authorization
     *
     * @return string
     */
    public function getAuthorizationUri()
    {
        $token = $this->getService()->requestRequestToken();
        return $this->getService()->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
    }

    /**
     * @return mixed
     */
    public function consumeData()
    {
        // TODO: Implement consumeData() method.
    }

    public function handleCallback()
    {
        // TODO: Implement handleCallback() method.
    }
}