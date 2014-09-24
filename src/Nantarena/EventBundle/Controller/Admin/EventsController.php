<?php

namespace Nantarena\EventBundle\Controller\Admin;

use Doctrine\ORM\ORMException;
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Form\Type\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EventsController
 *
 * @package Nantarena\EventBundle\Controller\Admin
 *
 * @Route("/admin/event/events")
 */
class EventsController extends Controller
{
    /**
     * @Route("/", name="nantarena_event_admin_events")
     * @Template()
     */
    public function listAction()
    {
        return array(
            'events' => $this->getDoctrine()->getRepository('NantarenaEventBundle:Event')->findBy(
                array(),
                array('startDate' => 'DESC')
            ),
        );
    }

    /**
     * @Route("/create", name="nantarena_event_admin_events_create")
     * @Template()
     */
    public function createAction(Request $request)
    {
        $event = new Event();

        $form = $this->createForm(new EventType(), $event, array(
            'action' => $this->generateUrl('nantarena_event_admin_events_create'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($event);
                $em->flush();

                $flashbag->add('success', $translator->trans('event.admin.events.create.flash_success'));
                return $this->redirect($this->generateUrl('nantarena_event_admin_events'));

            } catch (ORMException $e) {
                $flashbag->add('error', $translator->trans('event.admin.events.create.flash_error'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/edit/{id}", name="nantarena_event_admin_events_edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('NantarenaEventBundle:Event')->findWithAll($id);

        $originalTournaments = array();

        foreach ($event->getTournaments() as $tournament) {
            $originalTournaments[] = $tournament;
        }

        $form = $this->createForm(new EventType(), $event, array(
            'action' => $this->generateUrl('nantarena_event_admin_events_edit', array(
                'id' => $event->getId()
            )),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();

            try {
                foreach ($event->getTournaments() as $tournament) {
                    foreach ($originalTournaments as $key => $toDel) {
                        if ($toDel->getId() === $tournament->getId()) {
                            unset($originalTournaments[$key]);
                        }
                    }
                }

                foreach ($originalTournaments as $tournament) {
                    $em->remove($tournament);
                }

                $em->persist($event);
                $em->flush();

                $flashbag->add('success', $translator->trans('event.admin.events.edit.flash_success'));
                return $this->redirect($this->generateUrl('nantarena_event_admin_events'));

            } catch (ORMException $e) {
                $flashbag->add('error', $translator->trans('event.admin.events.edit.flash_error'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/delete/{id}", name="nantarena_event_admin_events_delete")
     * @Template()
     */
    public function deleteAction(Request $request, Event $event)
    {
        $translator = $this->get('translator');
        $flashbag = $this->get('session')->getFlashBag();

        $form = $this->createDeleteForm($event->getId());
        $form->handleRequest($request);

        $now = new \DateTime();

        if ($event->getStartRegistrationDate() <= $now) {
            $flashbag->add('error', $translator->trans('event.admin.events.delete.flash_error'));
            return $this->redirect($this->generateUrl('nantarena_event_admin_events'));
        }

        if ($form->isValid()) {
            try {
                if ($form->get('id')->getData() == $event->getId()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($event);
                    $em->flush();

                    $flashbag->add('success', $translator->trans('event.admin.events.delete.flash_success'));
                } else {
                    throw new \Exception;
                }
            } catch (ORMException $e) {
                $flashbag->add('error', $translator->trans('event.admin.events.delete.flash_error'));
            }

            return $this->redirect($this->generateUrl('nantarena_event_admin_events'));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to delete a Event entity by id.
     *
     * @param integer $id
     * @return \Symfony\Component\Form\Form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->add('submit', 'submit')
            ->setMethod('POST')
            ->setAction($this->generateUrl('nantarena_event_admin_events_delete', array(
                'id' => $id
            )))
            ->getForm();
    }
}
