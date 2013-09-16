<?php

namespace Nantarena\EventBundle\Validator\Constraints;

use Nantarena\EventBundle\Entity\Event;
use Symfony\Component\Validator\Constraint;

/**
 * Class TeamMembersTournamentsConstraint
 * @package Nantarena\EventBundle\Validator\Constraints
 * @Annotation
 */
class TeamMembersTournamentsConstraint extends Constraint
{
    public $message = 'event.team.members.tournaments';

    public function validatedBy()
    {
        return 'team_members_tournaments_constraint';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
