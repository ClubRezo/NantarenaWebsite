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
     * @Route("/{slug}/create", name="nantarena_payment_paymentprocess_create")
     * @Template()
     */
    public function createAction(Event $event, Request $request)
    {
        // Get user
        $user = $this->getUser();

        // Check if a transaction is running and delete it if possible
        $result = $this->cleanPaypalTransaction($event);
        if (!$result) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        // Get associated entry
        $entry = null;
        if (!$user->hasEntry($event, $entry)) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_signup'));
            return false;
        }

        $form = $this->createForm(new PaymentType(), new PaymentModel(), array(
            'action' => $this->get('nantarena_payment.payment_manager')->createPayment($event),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $now = new \DateTime();
            $total = $entry->getEntryType()->getPrice();

            $entity_payment = new PaypalPayment();
            $entity_payment
                ->setUser($user)
                ->setAmount($total)
                ->setValid(false)
                ->setDate($now)
            ;

            if ($total <= $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                $entity_payment
                    ->setState('-')
                    ->setPaymentID('-')
                    ->setPayerId('-')
                ;
            }

            $ent_trans = new Transaction();
            $ent_trans
                ->setPrice($entry->getEntryType()->getPrice())
                ->setUser($user)
                ->setEvent($event)
                ->setPayment($entity_payment)
            ;

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction(); // suspend auto-commit
            try {
                // look and work
                $em->persist($ent_trans);
                $em->persist($entity_payment);

                $em->flush();
                $em->getConnection()->commit();
            } catch (Exception $e) {
                $em->getConnection()->rollback();
                $em->close();
                // throw $e;
                $this->get('session')->getFlashBag()->add('error', 'Erreur de requête BDD');
            }

            $this->get('session')->getFlashBag()->add('success', "Une nouvelle transaction a été créée");
            if ($total > $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                return $this->redirect($this->generateUrl('nantarena_payment_paymentprocess_paypalpreconnection', 
                    array('slug' => $event->getSlug())));
            } else {
                return $this->redirect($this->generateUrl('nantarena_payment_paypalpayment_success', 
                    array('slug' => $event->getSlug())));
            }
        }

        return array(
            'entry' => $entry,
            'form' => $form->createView(),
        );
    }


    /**
     * @Route("/{slug}/paypal-pre-connection", name="nantarena_payment_paymentprocess_paypalpreconnection")
     * @Template()
     */
    public function paypalPreConnectionAction(Event $event)
    {
        $transaction = $this->getActivePaypalTransaction($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            $paypalpayment = $this->getPaypalPayment($transaction);
            if (!empty($paypalpayment->getPaymentId())
                || !empty($paypalpayment->getState())
                || !empty($paypalpayment->getPayerId())) {
                $this->get('session')->getFlashBag()->add('error', 'Les informations enregistrées ne correspondent pas à l\'étape de la procédure');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }
            if ($paypalpayment->getAmount() <= $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                $this->get('session')->getFlashBag()->add('error', 'Le montant est trop petit pour paypal');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }
        }

        return array(
            'event' => $event
        );
    }


    /**
     * @Route("/{slug}/paypal-connection", name="nantarena_payment_paypalpayment_paypalconnection")
     * @Template()
     */
    public function paypalConnectionAction(Event $event)
    {
        $user = $this->getUser();
        $transaction = $this->getActivePaypalTransaction($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            $paypalpayment = $this->getPaypalPayment($transaction);
            if (!empty($paypalpayment->getPaymentId())
                || !empty($paypalpayment->getState())
                || !empty($paypalpayment->getPayerId())) {
                $this->get('session')->getFlashBag()->add('error', 'Les informations enregistrées ne correspondent pas à l\'étape de la procédure');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }
            if ($paypalpayment->getAmount() <= $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                 $this->get('session')->getFlashBag()->add('error', 'Le montant est trop petit pour paypal');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }
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
                    array('State' => 'success', 'slug' => $event->getSlug()), true),
                $this->get('router')->generate('nantarena_payment_paypalpayment_paypalreturn', 
                    array('State' => 'cancel', 'slug' => $event->getSlug()), true));
        
            // Retrieve paypal url
            $redirectUrl = $paypal->getPaymentLink($payment);

            // Save state
            try {
                $em = $this->getDoctrine()->getManager();
                $paypalpayment->setPaymentID($payment->getId());
                $paypalpayment->setState($payment->getState());
                $em->flush();
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', 'Erreur de requête BDD');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }

            if (!empty($redirectUrl)) {
                return $this->redirect($redirectUrl);
            } else {
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
     * @Route("/{slug}/paypal-return", name="nantarena_payment_paypalpayment_paypalreturn")
     */
    public function paypalReturnAction(Event $event, Request $request)
    {
        $transaction = $this->getActivePaypalTransaction($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            $paypalpayment = $this->getPaypalPayment($transaction);
            if (empty($paypalpayment->getPaymentId())
                || empty($paypalpayment->getState())
                || !empty($paypalpayment->getPayerId())) {

                $this->get('session')->getFlashBag()->add('error', 'Les informations enregistrées ne correspondent pas à l\'étape de la procédure');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }
            if ($paypalpayment->getAmount() <= $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                 $this->get('session')->getFlashBag()->add('error', 'Le montant est trop petit pour paypal');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }
        }

        if ($request->query->get('State') === 'success') {
            // Save state
            try {
                $em = $this->getDoctrine()->getManager();
                $paypalpayment->setPayerId($request->query->get('PayerID'));
                $em->flush();
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', 'Erreur de requête BDD');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }

            return $this->redirect($this->generateUrl('nantarena_payment_paypalpayment_success', array('slug' => $event->getSlug())));
        } elseif ($request->query->get('State') === 'cancel') {
            
            $this->removePayment($paypalpayment);

            $this->get('session')->getFlashBag()->add('error', "La transaction a été annulée");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            $this->get('session')->getFlashBag()->add('error', "Un problème est survenu");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
    }


     /**
     * @Route("/{slug}/success", name="nantarena_payment_paypalpayment_success")
     * @Template()
     */
    public function successAction(Event $event)
    {
        $transaction = $this->getActivePaypalTransaction($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            $paypalpayment = $this->getPaypalPayment($transaction);
            if (empty($paypalpayment->getPaymentId())
                || empty($paypalpayment->getState())
                || empty($paypalpayment->getPayerId())) {
                $this->get('session')->getFlashBag()->add('error', 'Les informations enregistrées ne correspondent pas à l\'étape de la procédure');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }
        }

        return array(
            'trans' => $transaction,
        );
    }


    /**
     * @Route("/{slug}/pay", name="nantarena_payment_paypalpayment_pay")
     */
    public function payAction(Event $event)
    {
        $transaction = $this->getActivePaypalTransaction($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            $paypalpayment = $this->getPaypalPayment($transaction);
            if (empty($paypalpayment->getPaymentId())
                || empty($paypalpayment->getState())
                || empty($paypalpayment->getPayerId())) {
                $this->get('session')->getFlashBag()->add('error', 'Les informations enregistrées ne correspondent pas à l\'étape de la procédure');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }
        }

        try {
            if ($paypalpayment->getAmount() > $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                // Execution du paiement
                $paypal = $this->get('nantarena_payment.paypal_service');
                $payment = $paypal->executePayment(
                    $transaction->getPayment()->getPaymentId(),
                    $transaction->getPayment()->getPayerId()
                );
            }

            $this->get('session')->getFlashBag()->add('success', "Le paiement s'est bien déroulé - Merci");
            try {
                $em = $this->getDoctrine()->getManager();
                $transaction->getPayment()->setValid(True);
                $em->flush();
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', 'Erreur de requête BDD');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } catch (\PPConnectionException $ex) {

            $this->get('session')->getFlashBag()->add('error', $paypal->parseApiError($ex->getData()));
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } catch (\Exception $ex) {

            $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
    }

    /**
     * @Route("/{slug}/clean", name="nantarena_payment_paypalpayment_clean")
     */
    public function cleanAction(Event $event)
    {
        $transaction = $this->getActivePaypalTransaction($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            $paypalpayment = $this->getPaypalPayment($transaction);
            
            $this->removePayment($paypalpayment);

            $this->get('session')->getFlashBag()->add('error', "La transaction a été annulée");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
    }


    private function getActivePaypalTransaction($event)
    {
        $user = $this->getUser();

        // Get associated entry
        $entry = null;
        if (!$user->hasEntry($event, $entry)) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_signup'));
            return null;
        }

        $repository = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:Transaction');
        $transaction = $repository->findOneBy(array('user' => $entry->getUser(), 
            'event' => $entry->getEntryType()->getEvent(), 'refund' => null));

        if (!$transaction) {
            $this->get('session')->getFlashBag()->add('error', 'Auncune transaction en cours');
            return null;
        }

        $payment = $transaction->getPayment();

        if ($payment->getValid()) {
            $this->get('session')->getFlashBag()->add('error', 'Vous avez déjà payé');
            return null;
        }

        if (!($payment instanceof PaypalPayment)) {
            $this->get('session')->getFlashBag()->add('error', 'Un paiement administrateur est en cours pour votre compte');
            return null;
        }

        if ($payment->getUser() !== $user) {
            $this->get('session')->getFlashBag()->add('error', 'Votre coéquipier '.$payment->getUser()->getUsername().' est en train de payer votre place.');
            return null;
        }

        // check timeout
        $time_min = $this->container->getParameter('nantarena_payment.payment_timeout');
        $date = new \DateTime();
        $date->modify('-'.$time_min.' min');
        if ($date > $payment->getDate()) {
            $this->get('session')->getFlashBag()->add('error', 'La session est expirée');

            $this->removePayment($payment);
            return null;
        }

        // udate time
        try {
            $now = new \DateTime();
            $payment->setDate($now);
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', 'Erreur de requête BDD');
            return false;
        }
        
        return $transaction;
    }


    private function getPaypalPayment($transaction)
    {
        $payment = $transaction->getPayment();
        $paypalpayment = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:PaypalPayment')
            ->findOneById($transaction->getPayment()->getId());
        return $paypalpayment;
    }

    private function cleanPaypalTransaction($event)
    {
        $user = $this->getUser();

        // Get associated entry
        $entry = null;
        if (!$user->hasEntry($event, $entry)) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_signup'));
            return false;
        }

        $repository = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:Transaction');
        $transaction = $repository->findOneBy(array('user' => $entry->getUser(), 
            'event' => $entry->getEntryType()->getEvent(), 'refund' => null));

        // best case
        if (!$transaction) {
            return true;
        }

        $payment = $transaction->getPayment();

        if ($payment->getValid()) {
            $this->get('session')->getFlashBag()->add('error', 'Vous avez déjà payé');
            return false;
        }

        if (!($payment instanceof PaypalPayment)) {
            $this->get('session')->getFlashBag()->add('error', 'Un paiement administrateur est en cours pour votre compte');
            return false;
        }

        

        if ($payment->getUser() !== $user) {

            $time_min = $this->container->getParameter('nantarena_payment.payment_timeout');
            $time_secu = $this->container->getParameter('nantarena_payment.payment_delete_security_time');
            $time_tot = $time_min + $time_secu;

            $date = new \DateTime();
            $date->modify('-'.$time_tot.' min');
            $interval = $payment->getDate()->diff($date);
            $endDate = $interval->format('%i minutes et %s secondes');

            if ($date < $payment->getDate()) {
                $this->get('session')->getFlashBag()->add('error', 'Votre coéquipier '.$payment->getUser()->getUsername().' est en train de payer votre place.');
                $this->get('session')->getFlashBag()->add('error', 'Sa session expire dans '.$endDate);

                return false;
            } else {
                $this->get('session')->getFlashBag()->add('error', 'Votre coéquipier '.$payment->getUser()->getUsername().' avait commencé une transaction pour vous.');
            }
        }

        // clean transaction
        $this->removePayment($payment);
        
        $this->get('session')->getFlashBag()->add('success', 'La transaction non finie a été supprimée');
        return true;
    }

    private function removePayment(Payment $payment)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($payment);
            $em->flush();
            return true;
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', 'Erreur de requête BDD');
            return false;
        }
    }
}
