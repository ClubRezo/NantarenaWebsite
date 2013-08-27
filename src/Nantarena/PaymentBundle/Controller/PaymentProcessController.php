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
     * Première page
     * Affichage du formulaire des choix de paiement
     * Création d'une transaction et d'un paiement en BDD
     *
     * @Route("/{slug}/create", name="nantarena_payment_paymentprocess_create")
     * @Template()
     */
    public function createAction(Event $event, Request $request)
    {
        // Check if a transaction is running and delete it if possible
        $result = $this->cleanPaypalTransaction($event);
        if (!$result) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
        $user = $this->getUser();

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

            $paypalPayment = new PaypalPayment();
            $paypalPayment
                ->setUser($user)
                ->setAmount($total)
                ->setValid(false)
                ->setDate($now)
            ;

            if ($total <= $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                $paypalPayment
                    ->setState('-')
                    ->setPaymentID('-')
                    ->setPayerId('-')
                ;
            }

            $transaction = new Transaction();
            $transaction
                ->setPrice($entry->getEntryType()->getPrice())
                ->setUser($user)
                ->setEvent($event)
                ->setPayment($paypalPayment)
            ;

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction(); // suspend auto-commit
            try {
                // look and work
                $em->persist($transaction);
                $em->persist($paypalPayment);

                $em->flush();
                $em->getConnection()->commit();
            } catch (Exception $e) {
                $em->getConnection()->rollback();
                $em->close();
                // throw $e;
                $this->get('session')->getFlashBag()->add('error', 'Erreur de requête BDD');
            }

            $this->get('session')->getFlashBag()->add('success', 'Une nouvelle transaction a été créée');

            // Jump to success controller if payment is lower than minimal allowed paypal payment
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
     * Deuxième page
     * Explication du fonctionnement et bouton de connexion à paypal
     *
     * @Route("/{slug}/paypal-pre-connection", name="nantarena_payment_paymentprocess_paypalpreconnection")
     * @Template()
     */
    public function paypalPreConnectionAction(Event $event)
    {
        $transaction = $this->getTransactionStepOne($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        return array(
            'event' => $event
        );
    }


    /**
     * Troisième page (site externe)
     * Connexion à paypal
     * paramétrage de la requête paypal
     *
     * @Route("/{slug}/paypal-connection", name="nantarena_payment_paypalpayment_paypalconnection")
     */
    public function paypalConnectionAction(Event $event)
    {
        $transaction = $this->getTransactionStepOne($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
        $user = $this->getUser();
        $paypalPayment = $transaction->getPayment();

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
            $items = array($item);

            $payment = $paypal->paypalPaymentApproval(
                $total,
                'Paiement de l\'entrée à l\'évènement '.$event->getName(),
                $items,
                $this->get('router')->generate('nantarena_payment_paypalpayment_paypalreturn', 
                    array('state' => 'success', 'slug' => $event->getSlug()), true),
                $this->get('router')->generate('nantarena_payment_paypalpayment_paypalreturn', 
                    array('state' => 'cancel', 'slug' => $event->getSlug()), true)
            );
        
            // Retrieve paypal url
            $redirectUrl = $paypal->getPaymentLink($payment);

            // Save state
            try {
                $em = $this->getDoctrine()->getManager();
                $paypalPayment->setPaymentID($payment->getId());
                $paypalPayment->setState($payment->getState());
                $em->flush();
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', 'Erreur de requête BDD');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }

            if (!empty($redirectUrl)) {
                return $this->redirect($redirectUrl);
            } else {
                $this->get('session')->getFlashBag()->add('error', 'Quelque chose ne s\'est pas bien passé');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }

        } catch (\PPConnectionException $ex) {
            $this->get('session')->getFlashBag()->add('error', $paypal->parseApiError($ex->getData()));
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } catch (\Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
    }


    /**
     * Controller de retour paypal
     * redirige vers success ou cancel
     *
     * @Route("/{slug}/paypal-return/{state}", name="nantarena_payment_paypalpayment_paypalreturn")
     */
    public function paypalReturnAction(Event $event, $state, Request $request)
    {
        $transaction = $this->getTransactionStepTwo($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
        $paypalPayment = $transaction->getPayment();

        if ($state === 'success') {
            // Save state
            try {
                $em = $this->getDoctrine()->getManager();
                $paypalPayment->setPayerId($request->query->get('PayerID'));
                $em->flush();
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', 'Erreur de requête BDD');
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }

            return $this->redirect($this->generateUrl('nantarena_payment_paypalpayment_success', array('slug' => $event->getSlug())));
        } elseif ($state === 'cancel') {
            
            $this->removePayment($paypalPayment);

            $this->get('session')->getFlashBag()->add('error', "La transaction a été annulée");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            $this->get('session')->getFlashBag()->add('error', "Un problème est survenu");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
    }

     /**
     * Quatrième page
     * Récapitulatif de la commande
     *
     * @Route("/{slug}/success", name="nantarena_payment_paypalpayment_success")
     * @Template()
     */
    public function successAction(Event $event)
    {
        $transaction = $this->getTransactionStepThree($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        return array(
            'trans' => $transaction,
        );
    }


    /**
     * Validation du paiement
     *
     * @Route("/{slug}/pay", name="nantarena_payment_paypalpayment_pay")
     */
    public function payAction(Event $event)
    {
        $transaction = $this->getTransactionStepThree($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
        $paypalPayment = $transaction->getPayment();

        try {
            if ($paypalPayment->getAmount() > $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                // Execution du paiement
                $paypal = $this->get('nantarena_payment.paypal_service');
                $payment = $paypal->executePayment(
                    $transaction->getPayment()->getPaymentId(),
                    $transaction->getPayment()->getPayerId()
                );
            }

            try {
                $em = $this->getDoctrine()->getManager();
                $transaction->getPayment()->setValid(true);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Le paiement s\'est bien déroulé - Merci');
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
     * Annulation du paiement
     *
     * @Route("/{slug}/clean", name="nantarena_payment_paypalpayment_clean")
     */
    public function cleanAction(Event $event)
    {
        $transaction = $this->getActivePaypalTransaction($event);
        if (!$transaction) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            $paypalPayment = $transaction->getPayment();
            
            $this->removePayment($paypalPayment);

            $this->get('session')->getFlashBag()->add('error', "La transaction a été annulée");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
    }

    /**
     * Procédure de vérification
     * pour la première étape
     */
    private function getTransactionStepOne($event)
    {
        $transaction = $this->getActivePaypalTransaction($event);
        if (!$transaction) {
            return null;
        } else {
            $paypalPayment = $transaction->getPayment();
            if (!empty($paypalPayment->getPaymentId())
                || !empty($paypalPayment->getState())
                || !empty($paypalPayment->getPayerId())) {
                $this->get('session')->getFlashBag()->add('error', 'Les informations enregistrées ne correspondent pas à l\'étape de la procédure');
                return null;
            } elseif ($paypalPayment->getAmount() <= $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                $this->get('session')->getFlashBag()->add('error', 'Le montant est trop petit pour paypal');
                return null;
            } else {
                return $transaction;
            }
        }
    }

    /**
     * Procédure de vérification
     * pour la deuxième étape
     */
    private function getTransactionStepTwo($event)
    {
        $transaction = $this->getActivePaypalTransaction($event);
        if (!$transaction) {
            return null;
        } else {
            $paypalPayment = $transaction->getPayment();
            if (empty($paypalPayment->getPaymentId())
                || empty($paypalPayment->getState())
                || !empty($paypalPayment->getPayerId())) {
                $this->get('session')->getFlashBag()->add('error', 'Les informations enregistrées ne correspondent pas à l\'étape de la procédure');
                return null;
            } elseif ($paypalPayment->getAmount() <= $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                 $this->get('session')->getFlashBag()->add('error', 'Le montant est trop petit pour paypal');
                return null;
            } else {
                return $transaction;
            }
        }
    }

    /**
     * Procédure de vérification
     * pour la troisième étape
     */
    private function getTransactionStepThree($event)
    {
        $transaction = $this->getActivePaypalTransaction($event);
        if (!$transaction) {
            return null;
        } else {
            $paypalPayment = $transaction->getPayment();
            if (empty($paypalPayment->getPaymentId())
                || empty($paypalPayment->getState())
                || empty($paypalPayment->getPayerId())) {
                $this->get('session')->getFlashBag()->add('error', 'Les informations enregistrées ne correspondent pas à l\'étape de la procédure');
                return null;
            } else {
                return $transaction;
            }
        }
    }

    /**
     * Procédure de vérification
     * Récupérer en toute sécurité la transaction
     */
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

        if ($payment->isValid()) {
            $this->get('session')->getFlashBag()->add('error', 'Vous avez déjà payé');
            return null;
        }

        if (!$payment instanceof PaypalPayment) {
            $this->get('session')->getFlashBag()->add('error', 'Un paiement administrateur est en cours pour votre compte');
            return null;
        }

        if ($payment->getUser() !== $user) {
            $this->get('session')->getFlashBag()->add('error', 'Votre coéquipier '.$payment->getUser()->getUsername().' est en train de payer votre place.');
            return null;
        }

        // check timeout
        $minTime = $this->container->getParameter('nantarena_payment.payment_timeout');
        $date = new \DateTime();
        $date->modify('-'.$minTime.' min');
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
            return null;
        }
        
        return $transaction;
    }

    /**
     * Procédure de vérification et nottoyqge
     * Si une transaction existe, on la supprime si possible
     */
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

        if ($payment->isValid()) {
            $this->get('session')->getFlashBag()->add('error', 'Vous avez déjà payé');
            return false;
        }

        if (!$payment instanceof PaypalPayment) {
            $this->get('session')->getFlashBag()->add('error', 'Un paiement administrateur est en cours pour votre compte');
            return false;
        }

        if ($payment->getUser() !== $user) {

            $minTime = $this->container->getParameter('nantarena_payment.payment_timeout');
            $securityTime = $this->container->getParameter('nantarena_payment.payment_delete_security_time');
            $totalTime = $minTime + $securityTime;

            $date = new \DateTime();
            $date->modify('-'.$totalTime.' min');
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
