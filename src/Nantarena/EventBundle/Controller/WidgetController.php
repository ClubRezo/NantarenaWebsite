<?php

namespace Nantarena\EventBundle\Controller;

use Nantarena\EventBundle\Entity\Entry;
use Nantarena\EventBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        return array(
            'active' => ($event->getStartRegistrationDate() <= $now),
            'tournaments' => $tournaments,
        );
    }
}
