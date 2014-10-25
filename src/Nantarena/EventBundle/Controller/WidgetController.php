<?php

namespace Nantarena\EventBundle\Controller;

use Nantarena\EventBundle\Entity\Entry;
use Nantarena\EventBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nantarena\EventBundle\Entity\Tournament;
use Nantarena\EventBundle\Entity\Team;

/**
 * Class EventController
 *
 * @package Nantarena\EventBundle\Controller
 */
class WidgetController extends Controller
{
    /**
     * @Template()
     */
    public function tournamentsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime();

        /** @var Event $event */
        $event = $em->getRepository('NantarenaEventBundle:Event')->findNext();
        $tournaments = $em->getRepository('NantarenaEventBundle:Tournament')->findWithTeams($event);

        $teamService = $this->get('nantarena_event.team_service');
        $teamsValidation = array();

        /** @var Tournament $tournament */
        foreach($tournaments as $tournament) {
            /** @var Team $team */
            foreach($tournament->getTeams() as $team) {
                $teamsValidation[$team->getId()] = $teamService->isValid($team);
            }
        }

        return array(
            'active' => ($event->getStartRegistrationDate() <= $now),
            'tournaments' => $tournaments,
            'teamsValidation' => $teamsValidation
        );
    }
}
