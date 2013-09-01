<?php

namespace Nantarena\PaymentBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Manage routing
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
// Request for form
use Symfony\Component\HttpFoundation\Request;


// Entity
use Nantarena\PaymentBundle\Entity\Payment;
use Nantarena\PaymentBundle\Entity\Transaction;
use Nantarena\PaymentBundle\Entity\Refund;

use Nantarena\PaymentBundle\Form\Type\RefundType;

/**
 * Class RefundController
 *
 * @package Nantarena\PaymentBundle\Controller\Admin
 *
 * @Route("/admin/refund")
 */
class RefundController extends Controller
{
    /**
     * @Route("/list", name="nantarena_admin_refund_list")
     * @Template()
     */
    public function listAction()
    {
        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:Refund');
        $lrefund = $repository->findAll();
        // TODO get by event only

        return array('lrefund' => $lrefund);
    }

    /**
     * @Route("/payment/{id}", name="nantarena_admin_refund_payment")
     * @Template()
     */
    public function paymentAction(Payment $payment, Request $request)
    {
        if (!$payment) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.admin.refund.flash.no_pay'));
            return $this->redirect($this->generateUrl('nantarena_admin_payment_list'));
        }

        $refund = new Refund();
        $refund
            ->setUser($this->getUser())
            ->setDate(new \DateTime())
            ->setValid(true)
        ;

        $form = $this->createForm(new RefundType(), $refund, array(
            'action' => $this->generateUrl('nantarena_admin_refund_payment', array('id'=> $payment->getId())),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($refund);

            foreach ($payment->getTransactions() as $transaction) {
                $currRefund = $transaction->getRefund();
                if (empty($currRefund)) {
                    $transaction->setRefund($refund);
                }
            }
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('payment.admin.refund.flash.pay_refund'));
            return $this->redirect($this->generateUrl('nantarena_admin_payment_details', 
                    array('id' => $payment->getId())));
        }

        return array(
            'payment' => $payment,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/transaction/{id}", name="nantarena_admin_refund_transaction")
     * @Template()
     */
    public function transactionAction(Transaction $transaction, Request $request)
    {
        if (!$transaction) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.admin.refund.flash.no_trans'));
            return $this->redirect($this->generateUrl('nantarena_admin_payment_list'));
        }

        $currRefund = $transaction->getRefund();
        if (!empty($currRefund)) {
             $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.admin.refund.flash.is_refund'));
            return $this->redirect($this->generateUrl('nantarena_admin_payment_details', 
                    array('id' => $transaction->getPayment()->getId())));
        }

        $refund = new Refund();
        $refund
            ->setUser($this->getUser())
            ->setDate(new \DateTime())
            ->setValid(true)
        ;

        $form = $this->createForm(new RefundType(), $refund, array(
            'action' => $this->generateUrl('nantarena_admin_refund_transaction', array('id'=> $transaction->getId())),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($refund);

            $transaction->setRefund($refund);

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('payment.admin.refund.flash.pay_refund'));
            return $this->redirect($this->generateUrl('nantarena_admin_payment_details', 
                    array('id' => $transaction->getPayment()->getId())));
        }


        return array(
            'transaction' => $transaction,
            'form' => $form->createView(),
        );
    }

}