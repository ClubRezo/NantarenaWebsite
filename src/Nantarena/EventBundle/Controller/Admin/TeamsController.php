<?php

namespace Nantarena\EventBundle\Controller\Admin;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Nantarena\EventBundle\Entity\Entry;
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Entity\Team;
use Nantarena\EventBundle\Form\Type\EntryType;
use Nantarena\EventBundle\Form\Type\TeamType;
use Nantarena\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TeamsController
 *
 * @package Nantarena\EventBundle\Controller\Admin
 *
 * @Route("/admin/event/teams")
 */
class TeamsController extends Controller
{
    /**
     * @Route("/{slug}", name="nantarena_event_admin_teams",
     *    defaults={"slug" = null}
     * )
     * @Template()
     */
    public function listAction(Request $request, Event $event = null)
    {
        $db = $this->getDoctrine();

        if (null === $event) {
            if (null === ($event = $db->getRepository('NantarenaEventBundle:Event')->findNext()))
                return array();
        }

        $form = $this->createEventChoiceForm($event)->handleRequest($request);

        if ($form->isValid()) {
            $e = $form->get('event')->getData();
            return $this->redirect($this->generateUrl('nantarena_event_admin_teams', array(
                'slug' => $e->getSlug()
            )));
        }

        return array(
            'event' => $event,
            'teams' => $db->getRepository('NantarenaEventBundle:Team')->findByEvent($event),
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/create/{slug}", name="nantarena_event_admin_teams_create")
     * @Template()
     */
    public function createAction(Request $request, Event $event)
    {
        $team = new Team();

        $form = $this->createForm(new TeamType(), $team, array(
            'action' => $this->generateUrl('nantarena_event_admin_teams_create', array(
                'slug' => $event->getSlug()
            )),
            'method' => 'POST',
            'event' => $event,
            'em' => $this->get('doctrine.orm.entity_manager')
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {

            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($team);

                foreach($team->getMembers() as $member) {
                    $member->setTeam($team);
                    $em->persist($member);
                }

                $em->flush();

                $flashbag->add('success', $translator->trans('event.admin.teams.create.flash_success'));
                return $this->redirect($this->generateUrl('nantarena_event_admin_teams', array(
                    'slug' => $event->getSlug()
                )));

            } catch (ORMException $e) {
                $flashbag->add('error', $translator->trans('event.admin.teams.create.flash_error'));
            }
        }

        return array(
            'event' => $event,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/edit/{id}", name="nantarena_event_admin_teams_edit")
     * @Template()
     */
    public function editAction(Request $request, Team $team)
    {
        $form = $this->createForm(new TeamType(), $team, array(
            'action' => $this->generateUrl('nantarena_event_admin_teams_edit', array(
                'id' => $team->getId()
            )),
            'method' => 'POST',
            'event' => $team->getTournament()->getEvent(),
            'em' => $this->get('doctrine.orm.entity_manager')
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {

            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($team);
                foreach($team->getMembers() as $member) {
                    if($member->getTeam() == null) {
                        $member->setTeam($team);
                    }
                }
                $em->flush();

                $flashbag->add('success', $translator->trans('event.admin.teams.edit.flash_success'));
                return $this->redirect($this->generateUrl('nantarena_event_admin_teams', array(
                    'slug' => $team->getTournament()->getEvent()->getSlug()
                )));

            } catch (ORMException $e) {
                $flashbag->add('error', $translator->trans('event.admin.teams.edit.flash_error'));
            }
        }

        return array(
            'event' => $team->getTournament()->getEvent(),
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/delete/{id}", name="nantarena_event_admin_teams_delete")
     * @Template()
     */
    public function deleteAction(Request $request, Team $team)
    {
        $form = $this->createDeleteForm($team);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();

            try {
                if ($form->get('id')->getData() == $team->getId()) {
                    $em = $this->getDoctrine()->getManager();

                    foreach($team->getMembers() as $member) {
                        $member->setTeam(null);
                    }

                    $em->remove($team);
                    $em->flush();

                    $flashbag->add('success', $translator->trans('event.admin.teams.delete.flash_success'));
                } else {
                    throw new \Exception;
                }
            } catch (ORMException $e) {
                $flashbag->add('error', $translator->trans('event.admin.teams.delete.flash_error'));
            }

            return $this->redirect($this->generateUrl('nantarena_event_admin_teams', array(
                'slug' => $team->getTournament()->getEvent()->getSlug()
            )));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    private function createEventChoiceForm(Event $event)
    {
        return $this->createFormBuilder(array('event' => $event))
            ->add('event', 'entity', array(
                'class' => 'NantarenaEventBundle:Event',
                'property' => 'name',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.startDate', 'DESC');
                }
            ))
            ->setMethod('POST')
            ->getForm();
    }

    private function createDeleteForm(Team $team)
    {
        return $this->createFormBuilder(array('id' => $team->getId()))
            ->add('id', 'hidden')
            ->add('submit', 'submit')
            ->setMethod('POST')
            ->setAction($this->generateUrl('nantarena_event_admin_teams_delete', array(
                'id' => $team->getId()
            )))
            ->getForm();
    }
}
