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
        $exclusive = false;
        $t_tournaments = $value->getTournaments();

        // Déterminer si un des tournois auxquels participe l'équipe est exclusif
        foreach ($t_tournaments as $t_tournament) {
            if ($t_tournament->isExclusive()) {
                $exclusive = true;
                break;
            }
        }

        if ($exclusive) {
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

            foreach ($m_tournaments as $m_tournament) {
                if ($m_tournament->isExclusive()) {
                    $this->context->addViolation($constraint->message);
                    break;
                }
            }
        }
    }
}
