<?php

namespace Nantarena\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Nantarena\EventBundle\Entity\Event;
use Nantarena\UserBundle\Entity\Group;

/**
 * Class UserRepository
 *
 * @package Nantarena\UserBundle\Repository
 */
class UserRepository extends EntityRepository
{
    public function findAllWithGroups()
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.groups', 'g')
            ->addSelect('g')
            ->getQuery()
            ->getResult();
    }

    public function findAllInGroup(Group $group)
    {
        return $this->createQueryBuilder('u')
            ->where(':group MEMBER OF u.groups')
            ->setParameter('group', $group)
            ->getQuery()
            ->getResult();
    }

    public function findRegisteredEvent(Event $event)
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.entries', 'ee')
            ->leftJoin('ee.tournament', 'et')
            ->where('et.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }
}
