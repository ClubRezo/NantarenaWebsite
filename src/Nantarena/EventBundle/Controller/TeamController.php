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
 * Class TeamController
 *
 * @package Nantarena\EventBundle\Controller
 *
 * @Route("/event")
 *
 */
class TeamController extends Controller
{
    /**
     * @Route("/{slug}/team/create", name="nantarena_event_team_create")
     * @param Request $request
     * @param \Nantarena\EventBundle\Entity\Event $event
     * @return array
     * @Template()
     */
    public function createTeamAction(Request $request, Event $event)
    {
        $em = $this->getDoctrine()->getManager();
        $flashbag = $this->get('session')->getFlashBag();
        $translator = $this->get('translator');

        // Check if user is logged
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $creator = $this->get('security.context')->getToken()->getUser();

        /** @var Entry $entry */
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

                            foreach($team->getMembers() as $member) {
                                $member->setTeam($team);
                            }

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

    /** Edit a team
     * @Route("/{slug}/team/edit/{team}", name="nantarena_event_team_edit")
     * @param Request $request
     * @param \Nantarena\EventBundle\Entity\Event $event
     * @param \Nantarena\EventBundle\Entity\Team $team
     * @return \Symfony\Component\HttpFoundation\Response
     * @Template()
     */
    public function modifyTeamAction(Request $request, Event $event, Team $team)
    {
        $em = $this->getDoctrine()->getManager();
        $flashbag = $this->get('session')->getFlashBag();
        $translator = $this->get('translator');

        $user = $this->get('security.context')->getToken()->getUser();
        $entry = null;
        $user->hasEntry($event, $entry);

        //Can only modify team if is creator
        if($entry->getTeam() != null && $entry == $entry->getTeam()->getCreator()){
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

    /**
     * @Route("/{slug}/team/view/{team}", name="nantarena_event_team_view")
     * @param Team $team
     * @param Event $event
     * @return array
     * @Template()
     */
    public function viewTeamAction(Event $event, Team $team)
    {
        return array(
            'team' => $team,
            'event' => $event,
        );
    }
}
