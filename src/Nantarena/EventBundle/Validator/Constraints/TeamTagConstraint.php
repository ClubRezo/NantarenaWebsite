<?php

namespace Nantarena\EventBundle\Validator\Constraints;

use Nantarena\EventBundle\Entity\Event;
use Symfony\Component\Validator\Constraint;

/**
 * Class TeamTagConstraint
 * @package Nantarena\EventBundle\Validator\Constraints
 * @Annotation
 */
class TeamTagConstraint extends Constraint
{
    public $message = 'event.team.unique.tag';

    public function validatedBy()
    {
        return 'team_tag_constraint';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
