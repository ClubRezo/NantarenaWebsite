<?php

namespace Nantarena\EventBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class TeamMembersConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (count($value) == 0) {
            $this->context->addViolation($constraint->emptyMessage);
        } else {
            if (count($value) != count(array_unique($value->toArray()))) {
                $this->context->addViolation($constraint->sameMessage);
            }
        }
    }
}
