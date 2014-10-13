<?php

namespace Nantarena\EventBundle\Validator\Constraints;

use Doctrine\Common\Util\Debug;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class TeamMembersConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $membersId = array();

        foreach($value->getMembers() as $member) {
            $membersId[] = $member->getUser()->getId();

            if ($member->getTeam() != null && $member->getTeam()->getId() !== $value->getId()) {
                $this->context->addViolation($constraint->alreadyTeam);
            }
        }

        if (count($value->getMembers()) == 0) {
            $this->context->addViolation($constraint->emptyMessage);
        } else {
            if (count($membersId) != count(array_unique($membersId))) {
                $this->context->addViolation($constraint->sameMessage);
            }
        }
    }
}
