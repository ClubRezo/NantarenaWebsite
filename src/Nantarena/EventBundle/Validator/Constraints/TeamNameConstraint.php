<?php

namespace Nantarena\EventBundle\Validator\Constraints;

use Nantarena\EventBundle\Entity\Event;
use Symfony\Component\Validator\Constraint;

/**
 * Class TeamNameConstraint
 * @package Nantarena\EventBundle\Validator\Constraints
 * @Annotation
 */
class TeamNameConstraint extends Constraint
{
    public $message = 'event.team.unique.name';

    public function validatedBy()
    {
        return 'team_name_constraint';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
