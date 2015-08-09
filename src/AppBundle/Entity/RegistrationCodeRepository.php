<?php
/**
 * Created by PhpStorm.
 * User: Nate
 * Date: 8/8/15
 * Time: 6:36 PM
 */
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class RegistrationCodeRepository
 * @package AppBundle\Entity
 */
class RegistrationCodeRepository extends EntityRepository {
    /**
     * @param $code
     * @return null|object
     */
    public function findOneByCode($code)
    {
        return $this->findOneBy(['code' => $code]);
    }
}