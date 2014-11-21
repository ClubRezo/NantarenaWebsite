<?php

namespace Nantarena\PaymentBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Manage routing
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
// Request for form
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

// Entity
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Entity\Entry;

use Nantarena\PaymentBundle\Entity\Payment;
use Nantarena\PaymentBundle\Entity\PaypalPayment;

/**
 * Class PaymentController
 *
 * @package Nantarena\PaymentBundle\Controller\Admin
 *
 * @Route("/admin/payment/payment")
 */
class PaymentController extends Controller
{
    /**
     * @Route("/list/{slug}", name="nantarena_admin_payment_payment_list", defaults={"slug" = null})
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
            return $this->redirect($this->generateUrl('nantarena_admin_payment_payment_list', array(
                'slug' => $e->getSlug()
            )));
        }


        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:Payment');
        $lpayment = $repository->findValidPaymentByEvent($event);

        return array(
            'lpayment' => $lpayment,
            'event' => $event,
            'form' => $form->createView()
        );
    }

     /**
     * @Route("/details/{id}", name="nantarena_admin_payment_payment_details")
     * @Template()
     */
    public function detailsAction(Payment $payment)
    {
        return array(
            'payment' => $payment
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
}
