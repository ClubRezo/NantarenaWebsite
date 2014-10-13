<?php

namespace Nantarena\EventBundle\Validator\Constraints;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class TeamNameConstraintValidator extends ConstraintValidator
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        $teams = $this->em->getRepository('NantarenaEventBundle:Team')->createQueryBuilder('t')
            ->join('t.tournament', 'to')
            ->where('t.name = :name')
            ->andWhere('to.event = :event')
//            ->andWhere('t.id <> :id')
            ->setParameter('name', $value->getName())
            ->setParameter('event', $value->getTournament()->getEvent());
//            ->setParameter('id', $value->getId());
        if($value->getId() === null) {
            $teams->andWhere('t.id is not null');
        } else {
            $teams->andWhere('t.id <> :id')
                  ->setParameter('id', $value->getId());
        }
        $teams = $teams->getQuery()
                       ->getResult();


        if (count($teams) > 0) {
            $this->context->addViolationAt('name', $constraint->message);
        }
    }
}
