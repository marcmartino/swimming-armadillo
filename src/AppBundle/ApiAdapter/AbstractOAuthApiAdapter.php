<?php
namespace AppBundle\ApiAdapter;

/**
 * Class AbstractOAuthApiAdapter
 * @package AppBundle\ApiAdapter
 */
abstract class AbstractOAuthApiAdapter extends AbstractApiAdapter {
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
}