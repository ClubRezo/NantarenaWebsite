<?php

namespace Nantarena\EventBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\Common\Util\Debug;


class TeamMembersTournamentsConstraintValidator extends ConstraintValidator
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        // Parcourir tous les tournois auxquels les membres de l'équipe participent avec une autre équipe
        $m_tournaments = $this->em->getRepository('NantarenaEventBundle:Tournament')->createQueryBuilder('t')
            ->join('t.teams', 'te')
            ->join('te.members', 'u')
            ->where('u IN (:members)')
            ->andWhere('te <> :team')
            ->setParameter('members', $value->getMembers()->toArray())
            ->setParameter('team', ($value->getId() === NULL) ? '' : $value)
            ->getQuery()
            ->getResult()
        ;

        if (count($m_tournaments) > 0) {
            $this->context->addViolation($constraint->message);
        }
    }
}
