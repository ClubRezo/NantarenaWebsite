<?php

namespace Nantarena\EventBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\Common\Util\Debug;


class TeamMembersTournamentsConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $members = $value->getMembers();
        $tournament = $value->getTournament();

        foreach($members as $member) {
            if ($member->getTournament()->getId() !== $tournament->getId()) {
                $this->context->addViolation($constraint->message);
                break;
            }
        }
    }
}
