<?php

namespace Nantarena\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Manage routing
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

// Entity
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Entity\Entry;

use Nantarena\PaymentBundle\Entity\PaypalPayment;
use Nantarena\PaymentBundle\Entity\Payment;
use Nantarena\PaymentBundle\Entity\Refund;
use Nantarena\PaymentBundle\Entity\Transaction;


use Nantarena\PaymentBundle\Form\Type\PaymentType;
use Nantarena\PaymentBundle\Form\Model\Payment as PaymentModel;


// https://developer.paypal.com/webapps/developer/docs/integration/admin/manage-apps/
// https://developer.paypal.com/webapps/developer/docs/classic/lifecycle/goingLive/
// https://cms.paypal.com/uk/cgi-bin/?cmd=_render-content&content_ID=developer/howto_api_golivechecklist

/**
 * Class PaymentProcessController
 *
 * @package Nantarena\PaymentBundle\Controller\Admin
 *
 * @Route("/payment")
 */
class PaymentProcessController extends Controller
{
	/**
     * Si la personne n'a pas déjà payé
     * Affichage du formulaire des choix de paiement
     * Création d'une transaction et d'un paiement en BDD
     *
     * @Route("/create/{slug}", name="nantarena_payment_paymentprocess_create")
     * @Template()
     */
    public function createAction(Event $event, Request $request)
    {
        // Get user
        $user = $this->getUser();

        // Get associated entry
        $entry = null;
        if (!$user->hasEntry($event, $entry)) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_signup'));
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        $payment = $this->get('nantarena_payment.payment_service');
        if ($payment->isPaid($entry)) {
            $this->get('session')->getFlashBag()->add('error', 'Vous avez déjà payé');
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        // Check if a transaction is running and delete it if possible
        $transaction = $this->cleanTransaction($entry);
        if ($transaction) {
            $this->get('session')->getFlashBag()->add('error', 'Une personne de votre équipe paye actuellement pour vous');
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        $form = $this->createForm(new PaymentType(), new PaymentModel(), array(
            'action' => $this->get('nantarena_payment.payment_manager')->createPayment($event),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $now = new \DateTime();

            $entity_payment = new PaypalPayment();
            $entity_payment
                ->setUser($user)
                ->setAmount($entry->getEntryType()->getPrice())
                ->setValid(false)
                ->setDate($now)
            ;

            $ent_trans = new Transaction();
            $ent_trans
                ->setPrice($entry->getEntryType()->getPrice())
                ->setUser($user)
                ->setEvent($event)
                ->setPayment($entity_payment)
            ;

            $em = $this->getDoctrine()->getManager();
            $em->persist($ent_trans);
            $em->persist($entity_payment);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', "Une nouvelle transaction a été créée");
            return $this->redirect($this->generateUrl('nantarena_payment_paymentprocess_paypalpreconnection'));
        }

        return array(
            'entry' => $entry,
            'form' => $form->createView(),
        );
    }

    /**
     * Return null and delete transaction if necessary
     * or return transaction if it is impossible to delete it
     */
    private function cleanTransaction($entry)
    {
        $repository = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:Transaction');
        $transaction = $repository->findOneBy(array('user' => $entry->getUser()->getId(), 
            'event' => $entry->getEntryType()->getEvent(), 'refund' => null));

        if ($transaction) {
            $payment = $transaction->getPayment();
            if (!$payment->getValid() && $payment instanceof PaypalPayment 
                && $payment->getUser() === $entry->getUser()) {
                $this->get('session')->getFlashBag()->add('success', 'La transaction non finie a été supprimée');
                $em = $this->getDoctrine()->getManager();
                $em->remove($payment);
                $em->flush();

                return null;
            }
        }

        return $transaction;
    }


    /**
     * @Route("/paypal-pre-connection", name="nantarena_payment_paymentprocess_paypalpreconnection")
     * @Template()
     */
    public function paypalPreConnectionAction()
    {
        $user = $this->getUser();
        $transaction = $this->getActiveTransaction($user);
        if (!$transaction) {
            $this->get('session')->getFlashBag()->add('error', "Aucune transaction en cours");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            // check step
        }

        return array();
    }


    /**
     * @Route("/paypal-connection", name="nantarena_payment_paypalpayment_paypalconnection")
     * @Template()
     */
    public function paypalConnectionAction()
    {
        $user = $this->getUser();
        $transaction = $transaction = $this->getActiveTransaction();
        if (!$transaction) {
            $this->get('session')->getFlashBag()->add('error', "Aucune transaction en cours");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            // check step
        }

        // get associated entry
        $entry = null;
        $event = $transaction->getEvent();
        if (!$user->hasEntry($event, $entry))
        {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_signup'));
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        try {
            // Create paypal payment approval system
            $paypal = $this->get('nantarena_payment.paypal_service');

            $total = $transaction->getPayment()->getAmount();

            $item = $paypal->createItem($entry->getEntryType()->getName(), 1, $transaction->getPrice());
            $item_array = array($item);

            $payment = $paypal->paypalPaymentApproval(
                $total,
                "Paiement de l'entrée à l'évènement ".$event->getName(),
                $item_array,
                $this->get('router')->generate('nantarena_payment_paypalpayment_paypalreturn', 
                    array('State' => 'success'), true),
                $this->get('router')->generate('nantarena_payment_paypalpayment_paypalreturn', 
                    array('State' => 'cancel'), true));
        
            // Retrieve paypal url
            $redirectUrl = $paypal->getPaymentLink($payment);

            // Save state
            $paypalpayment = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:PaypalPayment')
                ->findOneById($transaction->getPayment()->getId());
            $em = $this->getDoctrine()->getManager();
            $paypalpayment->setPaymentID($payment->getId());
            $paypalpayment->setState($payment->getState());
            $em->flush();

            if (!empty($redirectUrl))
            {
                return $this->redirect($redirectUrl);
            } else
            {
                $this->get('session')->getFlashBag()->add('error', "Quelque chose ne s'est pas bien passé");
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }

        } catch (\PPConnectionException $ex) {
            $this->get('session')->getFlashBag()->add('error', $paypal->parseApiError($ex->getData()));
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } catch (\Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        return array();
    }


    /**
     * @Route("/paypal-return", name="nantarena_payment_paypalpayment_paypalreturn")
     */
    public function paypalReturnAction(Request $request)
    {
        $transaction = $transaction = $this->getActiveTransaction();
        if (!$transaction) {
            $this->get('session')->getFlashBag()->add('error', "Aucune transaction en cours");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            // check step
        }

        if ($request->query->get('State') === 'success') {
            // Save state
            $paypalpayment = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:PaypalPayment')
                ->findOneById($transaction->getPayment()->getId());
            $em = $this->getDoctrine()->getManager();
            $paypalpayment->setPayerId($request->query->get('PayerID'));
            $em->flush();

            return $this->redirect($this->generateUrl('nantarena_payment_paypalpayment_success'));
        } elseif ($request->query->get('State') === 'cancel') {
            $em = $this->getDoctrine()->getManager();
            $em->remove($transaction->getPayment());
            $em->flush();

            $this->get('session')->getFlashBag()->add('error', "La transaction a été annulée");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            $this->get('session')->getFlashBag()->add('error', "Un problème est survenu");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
    }


     /**
     * @Route("/success", name="nantarena_payment_paypalpayment_success")
     * @Template()
     */
    public function successAction(Request $request)
    {
        $transaction = $transaction = $this->getActiveTransaction();
        if (!$transaction) {
            $this->get('session')->getFlashBag()->add('error', "Aucune transaction en cours");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            // check step
        }

        return array(
            'trans' => $transaction,
        );
    }


    /**
     * @Route("/pay", name="nantarena_payment_paypalpayment_pay")
     * @Template()
     */
    public function payAction()
    {
        $transaction = $transaction = $this->getActiveTransaction();
        if (!$transaction) {
            $this->get('session')->getFlashBag()->add('error', "Aucune transaction en cours");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            // check step
        }

        if($transaction)
        {
            try {
                // Execution du paiement
                $paypal = $this->get('nantarena_payment.paypal_service');
                $payment = $paypal->executePayment(
                    $transaction->getPayment()->getPaymentId(),
                    $transaction->getPayment()->getPayerId());


                $this->get('session')->getFlashBag()->add('success', "Le paiement s'est bien déroulé - Merci");
                $em = $this->getDoctrine()->getManager();
                $transaction->getPayment()->setValid(True);
                $em->flush();
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            } catch (\PPConnectionException $ex) {

                $this->get('session')->getFlashBag()->add('error', $paypal->parseApiError($ex->getData()));
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            } catch (\Exception $ex) {

                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }
        }

       return array();
    }


    private function getActiveTransaction()
    {
        $user = $this->getUser();

        $repository = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:Transaction');
        $transaction = $repository->findOneBy(array('user' => $user->getId(), 'refund' => null));

        if ($transaction) {
            $payment = $transaction->getPayment();
            if (!$payment->getValid() && $payment instanceof PaypalPayment 
                && $payment->getUser() === $user) {
                return $transaction;
            }
        }
        return null;
    }

    /**
     * @Route("/clean", name="nantarena_payment_clean")
     */
    public function cleanAction()
    {
        $transaction = $transaction = $this->getActiveTransaction();
        if (!$transaction) {
            $this->get('session')->getFlashBag()->add('error', "Aucune transaction en cours");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            
            $this->get('session')->getFlashBag()->add('success', "Le transaction a été retrouvé - elle est supprimé");
            $em = $this->getDoctrine()->getManager();
            $em->remove($transaction);
            $em->remove($transaction->getPayment());
            $em->flush();
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

       return array();
    }


}
