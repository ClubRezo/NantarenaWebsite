<?php

namespace Nantarena\EventBundle\Controller\Admin;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Nantarena\EventBundle\Entity\Entry;
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Form\Type\EntryType;
use Nantarena\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class EntriesController
 *
 * @package Nantarena\EventBundle\Controller\Admin
 *
 * @Route("/admin/event/entries")
 */
class EntriesController extends Controller
{
    /**
     * @Route("/{slug}", name="nantarena_event_admin_entries",
     *    defaults={"slug" = null}
     * )
     * @Template()
     */
    public function listAction(Request $request, Event $event = null)
    {
        $db = $this->getDoctrine();

        if (null === $event) {
            if (null === ($event = $db->getRepository('NantarenaEventBundle:Event')->findNext())) {
                $event = $db->getRepository('NantarenaEventBundle:Event')->findLast();
            }
        }

        $form = $this->createEventChoiceForm($event)->handleRequest($request);

        if ($form->isValid()) {
            $e = $form->get('event')->getData();
            return $this->redirect($this->generateUrl('nantarena_event_admin_entries', array(
                'slug' => $e->getSlug()
            )));
        }

        $entries = $db->getRepository('NantarenaEventBundle:Entry')->findByEvent($event);

        $transactions = array();
        $paymentService = $this->get('nantarena_payment.payment_service');

        /** @var Entry $entry */
        foreach($entries as $entry) {
            $transactions[$entry->getId()] = $paymentService->getValidTransaction($entry);
        }

        return array(
            'event' => $event,
            'entries' => $entries,
            'form' => $form->createView(),
            'transactions' => $transactions
        );
    }

    /**
     * @Route("/create/{slug}", name="nantarena_event_admin_entries_create")
     * @Template()
     */
    public function createAction(Request $request, Event $event)
    {
        $entry = new Entry();

        $form = $this->createForm(new EntryType(), $entry, array(
            'action' => $this->generateUrl('nantarena_event_admin_entries_create', array(
                'slug' => $event->getSlug()
            )),
            'method' => 'POST',
            'event' => $event
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {

            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($entry);
                $em->flush();

                $flashbag->add('success', $translator->trans('event.admin.entries.create.flash_success'));
                return $this->redirect($this->generateUrl('nantarena_event_admin_entries', array(
                    'slug' => $event->getSlug()
                )));

            } catch (ORMException $e) {
                $flashbag->add('error', $translator->trans('event.admin.entries.create.flash_error'));
            }
        }

        return array(
            'event' => $event,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/edit/{slug}/{user_id}", name="nantarena_event_admin_entries_edit")
     * @ParamConverter("event", class="NantarenaEventBundle:Event", options={"slug"="slug"})
     * @ParamConverter("user", class="NantarenaUserBundle:User", options={"id"="user_id"})
     * @Template()
     */
    public function editAction(Request $request, Event $event, User $user)
    {
        /** @var Entry $entry */
        $entry = $this->getDoctrine()->getRepository('NantarenaEventBundle:Entry')->findByEventAndUser($event, $user);

        if (null === $entry)
            return new NotFoundHttpException();

        $form = $this->createForm(new EntryType(), $entry, array(
            'action' => $this->generateUrl('nantarena_event_admin_entries_edit', array(
                'slug' => $event->getSlug(),
                'user_id' => $user->getId()
            )),
            'method' => 'POST',
            'event' => $event,
            'edit' => true
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();

            try {
                $em = $this->getDoctrine()->getManager();

                $em->persist($entry);
                $em->flush();

                $flashbag->add('success', $translator->trans('event.admin.entries.edit.flash_success'));
                return $this->redirect($this->generateUrl('nantarena_event_admin_entries', array(
                    'slug' => $event->getSlug()
                )));

            } catch (ORMException $e) {
                $flashbag->add('error', $translator->trans('event.admin.entries.edit.flash_error'));
            }
        }

        return array(
            'event' => $event,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/delete/{slug}/{user_id}", name="nantarena_event_admin_entries_delete")
     * @ParamConverter("event", class="NantarenaEventBundle:Event", options={"slug"="slug"})
     * @ParamConverter("user", class="NantarenaUserBundle:User", options={"id"="user_id"})
     * @Template()
     */
    public function deleteAction(Request $request, Event $event, User $user)
    {
        $form = $this->createDeleteForm($event, $user);
        $form->handleRequest($request);

        $entry = $this->getDoctrine()->getRepository('NantarenaEventBundle:Entry')->findByEventAndUser($event, $user);

        if (null === $entry)
            return new NotFoundHttpException();

        if ($form->isValid()) {
            $translator = $this->get('translator');
            $flashbag = $this->get('session')->getFlashBag();

            try {
                if ($form->get('event_id')->getData() == $event->getId() && $form->get('user_id')->getData() == $user->getId()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($entry);
                    $em->flush();

                    $flashbag->add('success', $translator->trans('event.admin.entries.delete.flash_success'));
                } else {
                    throw new \Exception;
                }
            } catch (ORMException $e) {
                $flashbag->add('error', $translator->trans('event.admin.entries.delete.flash_error'));
            }

            return $this->redirect($this->generateUrl('nantarena_event_admin_entries', array(
                'slug' => $event->getSlug()
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

    private function createDeleteForm(Event $event, User $user)
    {
        return $this->createFormBuilder(array('event_id' => $event->getId(), 'user_id' => $user->getId()))
            ->add('event_id', 'hidden')
            ->add('user_id', 'hidden')
            ->add('submit', 'submit')
            ->setMethod('POST')
            ->setAction($this->generateUrl('nantarena_event_admin_entries_delete', array(
                'slug' => $event->getSlug(),
                'user_id' => $user->getId()
            )))
            ->getForm();
    }
}
