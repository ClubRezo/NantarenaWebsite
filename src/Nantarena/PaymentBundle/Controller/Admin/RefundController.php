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
 * @Route("/admin/payment/refund")
 */
class RefundController extends Controller
{
    /**
     * @Route("/list", name="nantarena_admin_payment_refund_list")
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
     * @Route("/payment/{id}", name="nantarena_admin_payment_refund_payment")
     * @Template()
     */
    public function paymentAction(Payment $payment, Request $request)
    {
        if (!$payment) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.admin.refund.flash.no_pay'));
            return $this->redirect($this->generateUrl('nantarena_admin_payment_refund_list'));
        }

        $refund = new Refund();
        $refund
            ->setUser($this->getUser())
            ->setDate(new \DateTime())
            ->setValid(true)
        ;

        $form = $this->createForm(new RefundType(), $refund, array(
            'action' => $this->generateUrl('nantarena_admin_payment_refund_payment', array('id'=> $payment->getId())),
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
            return $this->redirect($this->generateUrl('nantarena_admin_payment_payment_details', 
                    array('id' => $payment->getId())));
        }

        return array(
            'payment' => $payment,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/transaction/{id}", name="nantarena_admin_payment_refund_transaction")
     * @Template()
     */
    public function transactionAction(Transaction $transaction, Request $request)
    {
        if (!$transaction) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.admin.refund.flash.no_trans'));
            return $this->redirect($this->generateUrl('nantarena_admin_payment_payment_list'));
        }

        $currRefund = $transaction->getRefund();
        if (!empty($currRefund)) {
             $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.admin.refund.flash.is_refund'));
            return $this->redirect($this->generateUrl('nantarena_admin_payment_payment_details', 
                    array('id' => $transaction->getPayment()->getId())));
        }

        $refund = new Refund();
        $refund
            ->setUser($this->getUser())
            ->setDate(new \DateTime())
            ->setValid(true)
        ;

        $form = $this->createForm(new RefundType(), $refund, array(
            'action' => $this->generateUrl('nantarena_admin_payment_refund_transaction', array('id'=> $transaction->getId())),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($refund);

            $transaction->setRefund($refund);

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('payment.admin.refund.flash.pay_refund'));
            return $this->redirect($this->generateUrl('nantarena_admin_payment_payment_details', 
                    array('id' => $transaction->getPayment()->getId())));
        }

        return array(
            'transaction' => $transaction,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/modify/{id}", name="nantarena_admin_payment_refund_modify")
     * @Template()
     */
    public function modifyAction(Refund $refund, Request $request)
    {
        $form = $this->createForm(new RefundType(), $refund, array(
            'action' => $this->generateUrl('nantarena_admin_payment_refund_modify', array('id'=> $refund->getId())),
            'method' => 'POST',
        ));

        $res = $refund->getPayment();
        if (!empty($res)) {
            $payId = $res->getId();
        } else {
            return array();
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('payment.admin.refund.flash.modify_refund'));
            return $this->redirect($this->generateUrl('nantarena_admin_payment_payment_details', array('id' => $payId)));
        }

        return array(
            'id_payment' => $payId,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/validate/{id}", name="nantarena_admin_payment_refund_validate")
     */
    public function validateAction(Refund $refund)
    {
        $res = $refund->getPayment();
        if (!empty($res)) {
            $payId = $res->getId();
        } else {
            return array();
        }

        $em = $this->getDoctrine()->getManager();
        $refund->setValid(true);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('payment.admin.refund.flash.validate_refund'));
        return $this->redirect($this->generateUrl('nantarena_admin_payment_payment_details', array('id' => $payId)));
    }

}