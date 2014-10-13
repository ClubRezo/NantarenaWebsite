<?php

namespace Nantarena\EventBundle\Controller;

use Nantarena\EventBundle\Entity\Entry;
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Entity\Team;
use Nantarena\EventBundle\Form\Type\TeamType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProfileController
 *
 * @package Nantarena\EventBundle\Controller
 *
 */
class ProfileController extends Controller
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $em = $this->get('doctrine')->getManager();
        $event = $em->getRepository('NantarenaEventBundle:Event')->findNext();

        return array(
            'entries' => $user->getEntries(),
            'nextEvent' => $event
        );
    }

    /**
     * @param Request $request
     * @Route("profile/team/create/{slug}", name="nantarena_profile_create_team")
     * @Template()
     * @param \Nantarena\EventBundle\Entity\Event $event
     * @return array
     */
    public function createTeamAction(Request $request, Event $event)
    {
        $em = $this->getDoctrine()->getManager();
        $flashbag = $this->get('session')->getFlashBag();
        $translator = $this->get('translator');

        $creator = $this->get('security.context')->getToken()->getUser();
        $entry = null;
        // if user has an entry, create team
        if(($creator->hasEntry($event, $entry)) === true){
            if($entry->getTeam() == null) {
                $team = new Team();
                $team->setCreator($entry);
                $team->addMember($entry);
                $team->setTournament($entry->getTournament());

                $form = $this->createForm(new TeamType(), $team, array(
                    'em' => $em,
                    'event' => $event));
                $form->remove('creator')
                    ->remove('tournament');

                if($request->getMethod() === 'POST') {
                    $form->handleRequest($request);
                    if($form->isValid()) {
                        try {
                            $em->persist($team);
                            $em->flush();
                            $flashbag->add('success', $translator->trans('event.profile.createTeam.success'));
                        } catch (\Exception $e) {
                            $flashbag->add('error', $translator->trans('event.profile.createTeam.error'));
                        }
                    }
                }
                return array(
                    'form' => $form->createView(),
                    'event' => $event,
                );
            } else {
                $this->get('session')->getFlashBag()->add('error', $translator->trans('event.profile.createTeam.teamExists'));
                return $this->redirect($this->generateUrl('nantarena_event_participate', array(
                    'slug' => $event->getSlug(),
                )));
            }
        } else {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('event.profile.createTeam.error'));
            return $this->redirect($this->generateUrl('nantarena_event_participate', array(
                'slug' => $event->getSlug(),
            )));
        }

    }

    /** Modify a  team
     * @param \Nantarena\EventBundle\Entity\Team $team
     * @Route("profile/team/modify/{slug}/{team}", name="nantarena_profile_modify_team")
     * @param Request $request
     *
     * @param \Nantarena\EventBundle\Entity\Event $event
     * @return \Symfony\Component\HttpFoundation\Response
     * @template
     */
    public function modifyTeamAction(Team $team, Request $request, Event $event)
    {
        $em = $this->getDoctrine()->getManager();
        $flashbag = $this->get('session')->getFlashBag();
        $translator = $this->get('translator');

        $user = $this->get('security.context')->getToken()->getUser();
        $entry = null;
        $user->hasEntry($event, $entry);

        //Can only modify team if is part of it
        if($entry->getTeam() != null && $entry->getTeam()->getId() === $team->getId()){
            $form = $this->createForm(new TeamType(), $team, array(
                'em' => $em,
                'event' => $event));
                    $form->remove('creator')
                        ->remove('tournament');

            if($request->getMethod() === 'POST') {
                $form->handleRequest($request);
                if($form->isValid()) {
                    foreach($team->getMembers() as $member) {
                        if($member->getTeam() == null) {
                            $member->setTeam($team);
                        }
                    }
                    try {
                        $em->flush();
                        $flashbag->add('success', $translator->trans('event.profile.modifyTeam.success'));
                        return $this->redirect($this->generateUrl('nantarena_event_show', array(
                            'slug' => $event->getSlug()
                            )));
                    } catch (\Exception $e) {
                        $flashbag->add('error', $translator->trans('event.profile.modifyTeam.error'));
                    }
                }
            }
            return array(
                'form' => $form->createView(),
                'event' => $event,
            );
        }else{
            $flashbag->add('error', $translator->trans('event.profile.modifyTeam.notInTeam'));
            return $this->redirect($this->generateUrl('nantarena_event_show', array(
                'slug' => $event->getSlug()
            )));
        }

    }

}