<?php

namespace Nantarena\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Nantarena\UserBundle\Entity\Group;

/**
 * Class GroupRepository
 *
 * @package Nantarena\UserBundle\Repository
 */
class GroupRepository extends EntityRepository
{
    public function findAllWithCount()
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.users', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }
}
