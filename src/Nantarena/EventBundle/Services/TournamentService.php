<?php

namespace Nantarena\EventBundle\Services;

use Nantarena\EventBundle\Entity\Tournament;
use Nantarena\EventBundle\Entity\Team;
use Nantarena\EventBundle\Services\TeamService;

class TournamentService
{
    /** @var TeamService $payment */
    protected $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * @param Tournament $tournament
     * @return bool
     */
    public function isComplete(Tournament $tournament) {
        $validTeams = 0;

        /** @var Team $team */
        foreach ($tournament->getTeams() as $team) {
            if ($this->teamService->isValid($team)) {;
                $validTeams++;
            }
        }

        return ($validTeams >= $tournament->getMaxTeams());
    }
}
