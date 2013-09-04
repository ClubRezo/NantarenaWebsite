<?php

namespace Nantarena\EventBundle\Validator\Constraints;

use Nantarena\EventBundle\Entity\Event;
use Symfony\Component\Validator\Constraint;

/**
 * Class TeamCreatorConstraint
 * @package Nantarena\EventBundle\Validator\Constraints
 * @Annotation
 */
class TeamCreatorConstraint extends Constraint
{
    public $message = 'event.team.creator';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
