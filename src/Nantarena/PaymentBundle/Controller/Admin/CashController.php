<?php

namespace Nantarena\PaymentBundle\Controller\Admin;

use Nantarena\PaymentBundle\Entity\Transaction;
use Nantarena\PaymentBundle\Form\Type\PaymentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Manage routing
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
// Request for form
use Symfony\Component\HttpFoundation\Request;

// Entity
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Entity\Entry;

use Nantarena\PaymentBundle\Entity\Payment;
use Nantarena\PaymentBundle\Entity\CashPayment;

/**
 * Class CashController
 *
 * @package Nantarena\PaymentBundle\Controller\Admin
 *
 * @Route("/admin/payment/cash")
 */
class CashController extends Controller
{
    /**
     * @Route("/entry/{id}", name="nantarena_admin_payment_cash_user")
     * @Template()
     */
    public function payUserAction(Request $request, Entry $entry)
    {
        $form = $this->createFormBuilder(array('entry_id' => $entry->getId()))
            ->add('entry_id', 'hidden')
            ->add('submit', 'submit')
            ->setMethod('POST')
            ->setAction($this->generateUrl('nantarena_admin_payment_cash_user', array(
                'id' => $entry->getId()
            )))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $transaction = new Transaction();
            $transaction->setUser($entry->getUser());
            $transaction->setEvent($entry->getTournament()->getEvent());
            $transaction->setPrice(0);

            $payment = new CashPayment();
            $payment->setUser($entry->getUser());
            $payment->setAdmin($this->getUser());
            $payment->addTransaction($transaction);
            $payment->setDate(new \DateTime());
            $payment->setValid(true);

            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($transaction);
            $em->persist($payment);
            $em->flush();

            $flashbag = $this->get('session')->getFlashBag();
            $translator = $this->get('translator');

            $flashbag->add('success', $translator->trans('payment.admin.payment.cash.success'));
            return $this->redirect($this->generateUrl('nantarena_event_admin_entries', array('slug' => $entry->getTournament()->getEvent()->getSlug())));
        }

        return array(
        	'entry' => $entry,
            'form' => $form->createView()
        );
    }
}
