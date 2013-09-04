<?php

namespace Nantarena\EventBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class TeamMembersConstraint
 * @package Nantarena\EventBundle\Validator\Constraints
 * @Annotation
 */
class TeamMembersConstraint extends Constraint
{
    public $emptyMessage = 'event.team.members.empty';
    public $sameMessage = 'event.team.members.same';
}
