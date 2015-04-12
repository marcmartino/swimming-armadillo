<?php

namespace AppBundle\ABTest;

use AppBundle\Entity\ABTest;
use AppBundle\Entity\Insight;
use AppBundle\Entity\User;
use Doctrine\DBAL\Driver\Connection;
use AppBundle\Insight\InsightService;

class ABTestService {

    /** @var User */
    protected $user;

    /**
     * @var InsightService
     */
    protected $insightService;

    /**
     * @param InsightService $insightService
     */
    public function __construct(
        InsightService $insightService
    )
    {
        $this->insightService = $insightService;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param ABTest $abTest
     * @return array
     */
    public function getInsights(AbTest $abTest)
    {
        return $this->insightService->getInsights($abTest, $this->getUser());
    }

}