<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Provider;
use AppBundle\MeasurementType\MeasurementType;
use AppBundle\Provider\Providers;
use AppBundle\UnitType\UnitType;
use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\ServiceFactory;
use OAuth\Common\Storage\Session;
use AppBundle\OAuth\WithingsOAuth;
use OAuth\Common\Consumer\Credentials;
use AppBundle\ApiAdapter\WithingsApiAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->forward('AppBundle:Default:services');
        }
        return $this->render('default/index.html.twig');
    }


    /**
     * @Route("/providers", name="providers")
     */
    public function servicesAction()
    {
        /** @var Provider $provider */
        $provider = $this->get('entity_provider');
        return $this->render("default/providers.html.twig", [
            'providers' => $provider->getProviders($this->getUser()->getId())
        ]);
    }

    /**
     * @Route("/view/data")
     */
    public function viewDataAction()
    {
        return $this->render("default/view_data.html.twig");
    }

    /**
     * @Route("/withings/callback")
     */
    public function withingsCallback()
    {
        /** @var WithingsApiAdapter $withingsAdapter */
        $withingsAdapter = $this->get('withings_api_adapter');
        $withingsAdapter->getWithingsService()->getStorage()->retrieveAccessToken('WithingsOAuth');

        $accessToken = $withingsAdapter->getAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);

        /** @var Provider $provider */
        $provider = $this->get('entity_provider');

        $conn = $this->get('database_connection');
        $stmt = $conn->prepare("INSERT INTO oauth_access_tokens (user_id, service_provider_id, foreign_user_id, token, secret) VALUES (:userId, :providerId, :foreignUserId, :accessToken, :accessTokenSecret)");
        $stmt->execute([
            ':userId' => $this->getUser()->getId(),
            ':providerId' => $provider->getProvider(Providers::WITHINGS)[0]['id'],
            ':foreignUserId' => $_GET['userid'],
            ':accessToken' => $accessToken->getAccessToken(),
            ':accessTokenSecret' => $accessToken->getAccessTokenSecret(),
        ]);

        return $this->redirect($this->generateUrl('providers'));
    }

    /**
     * @Route("/withings/authorize", name="withingsauthorize")
     */
    public function authorizeWithings()
    {
        /** @var WithingsApiAdapter $withingsAdapter */
        $withingsAdapter = $this->get('withings_api_adapter');
        $url = $withingsAdapter->getAuthorizationUrl();
        return new RedirectResponse((string) $url);
    }

    /**
     * @Route("/withings/data", name="withingsdata")
     */
    public function displayWithingData()
    {
        /** @var WithingsApiAdapter $withingsAdapter */
        $withingsAdapter = $this->get('withings_api_adapter');
        $token = $withingsAdapter->getWithingsService()->getStorage()->retrieveAccessToken('WithingsOAuth');

        // TODO un-hardcode user id
        $uri = 'measure?action=getmeas&userid=5575888';

        $response = $withingsAdapter->getWithingsService()->request($uri);

        $json = json_decode($response, true);

        if ($json['status'] !== 0) {
            throw new \Exception("Request was unsuccessful.");
        }

        /** @var \PDO $conn */
        $conn = $this->get('database_connection');
        $eventQuery = 'INSERT INTO measurement_event (event_time, provider_id) VALUES (:event_time, :provider_id)';
        $eventStmt = $conn->prepare($eventQuery);
        $measurementQuery = 'INSERT INTO measurement (measurement_event_id, measurement_type_id, units_type_id, units) VALUES (:measurement_event_id, :measurement_type_id, :units_type_id, :units)';
        $measurementStmt = $conn->prepare($measurementQuery);

        $types = [];

        foreach ($json['body']['measuregrps'] as $measureGroup) {
            $datetime = date("Y-m-d H:i:s", $measureGroup['date']);
            $measurements = $measureGroup['measures'];
            $eventStmt->execute([
                ':event_time' => $datetime,
                ':provider_id' => 1
            ]);

            $eventId = $conn->lastInsertId('measurement_event_id_seq');

            foreach ($measurements as $measurement) {

                $measurementTypeId = false;
                $unitsTypeId = false;
                $units = $measurement['value'];

                switch ($measurement['type']) {
                    case 1:  // weight
                        $measurementTypeId = 2;
                        $unitsTypeId = 3;
                        $units = $measurement['value'];
                        break;
                    case 4:  // height
                        $measurementTypeId = 3;
                        $unitsTypeId = 4;
                        break;
                    case 5:  // fat free mass
                        $measurementTypeId = 4;
                        $unitsTypeId = 3;
                        $units = $measurement['value'];
                        break;
                    case 6:  // fat ratio
                        $measurementTypeId = 5;
                        $unitsTypeId = 2;
                        $units = $measurement['value'] * pow(10, $measurement['unit']);
                        break;
                    case 8:  // fat mass weight
                        $measurementTypeId = 6;
                        $unitsTypeId = 3;
                        break;
                    case 11: // heart pulse
                        $measurementTypeId = 1;
                        $unitsTypeId = 1;
                        break;
                    default:
                        throw new \Exception("Measurement type (" . $measurement['type'] . ") not handled");

                }

                $measurementStmt->execute([
                    ':measurement_event_id' => $eventId,
                    ':measurement_type_id' => $measurementTypeId,
                    ':units_type_id' => $unitsTypeId,
                    ':units' => $units
                ]);
            }

        }

        exit;
    }

    /**
     * @Route("/withings/store_token")
     */
    public function withingsStoreToken()
    {
        /** @var WithingsApiAdapter $withingsAdapter */
        $withingsAdapter = $this->get('withings_api_adapter');

        $token = new StdOAuth1Token();
        $token->setAccessToken('318dc8541257c51654462e3df777ffdd2fd81430301e7c806379bd769cbc');
        $token->setAccessTokenSecret('20e4e4e76d19936fefcd0bfd7ef803f6690c7089768f2d37e5cffce393bee');

        $withingsAdapter->getWithingsService()->getStorage()->storeAccessToken('WithingsOAuth', $token);
        exit;
    }

    /**
     * @Route("/phpinfo")
     */
    public function showPHPInfo()
    {
        phpinfo();
    }
}
