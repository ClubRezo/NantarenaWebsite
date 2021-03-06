<?php

namespace Nantarena\ForumBundle\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function findAllWithForums()
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->addSelect('c, f')
            ->join('c.forums', 'f')
            ->addOrderBy('c.position')
            ->addOrderBy('f.position');

        return $qb->getQuery()->getResult();
    }

    public function findWithJoins($id)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->addSelect('c, f')
            ->join('c.forums', 'f')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getSingleResult();
    }
}
