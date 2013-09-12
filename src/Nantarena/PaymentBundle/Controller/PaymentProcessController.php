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

use Nantarena\PaymentBundle\Exception\ConflictException;


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
        if (!$this->cleanPaypalTransaction($event)) {
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }
        $user = $this->getUser();

        // Get associated entry
        $entry = null;
        $user->hasEntry($event, $entry);

        // Get partners
        $userList = array();
        foreach ($user->getTeams() as $team) {
            if ($team->getEvent() === $event) {
                foreach ($team->getMembers() as $member) {
                    if ($member !== $user) {
                        if (!in_array($member, $userList)) {
                            array_push($userList, $member);
                        }
                    }
                }
            }
        }

        $model = new PaymentModel();
        $form = $this->createForm(new PaymentType(), $model, array(
            'action' => $this->get('nantarena_payment.payment_manager')->createPayment($event),
            'method' => 'POST',
            'userList' => $userList,
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {

            $now = new \DateTime();

            $paypalPayment = new PaypalPayment();
            $paypalPayment
                ->setUser($user)
                ->setValid(false)
                ->setDate($now)
            ;

            // Create all transactions
            $allTransactions = array();

            // user transaction
            $transaction = new Transaction();
            $transaction
                ->setPrice($entry->getEntryType()->getPrice())
                ->setUser($user)
                ->setEvent($event)
                ->setPayment($paypalPayment)
            ;
            array_push($allTransactions, $transaction);


            // partners transaction
            foreach ($model->getPartners() as $partner) {
                $entry = null;
                $partner->hasEntry($event, $entry);
                if ($enry) {
                    $transaction = new Transaction();
                    $transaction
                        ->setPrice($entry->getEntryType()->getPrice())
                        ->setUser($user)
                        ->setEvent($event)
                        ->setPayment($paypalPayment)
                    ;
                    array_push($allTransactions, $transaction);
                }
            }



            // jump paypal system if the amout is too low
            $total = 0;
            foreach ($allTransactions as $transaction) {
                $total += $transaction->getPrice();
            }
            if ($total <= $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                $paypalPayment
                    ->setState('-')
                    ->setPaymentID('-')
                    ->setPayerId('-')
                ;
            }



            $validator = $this->container->get('validator');

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction(); // suspend auto-commit
            try {
                $em->persist($paypalPayment);
                $em->flush();

                foreach ($allTransactions as $transaction) {
                    $errorList = $validator->validate($transaction);
                    if (count($errorList) > 0) {
                        foreach ($errorList as $err) {
                            $this->get('session')->getFlashBag()->add('error', $err->getMessage());
                        }
                        
                        throw new ConflictException('');
                    }
                    $em->persist($transaction);
                    $em->flush();
                }

                $em->getConnection()->commit();
            } catch (\Exception $e) {
                $em->getConnection()->rollback();
                $em->close();

                if ($e instanceof ConflictException) {
                    return $this->redirect($this->generateUrl('nantarena_user_profile'));
                } else {
                    // throw $e;
                    $this->get('session')->getFlashBag()->add('error', 'Erreur de requête BDD ('.$e->getMessage().')');
                    return $this->redirect($this->generateUrl('nantarena_user_profile'));
                }
            }

            $this->get('session')->getFlashBag()->add('success', 'Une nouvelle transaction a été créée');

            // Jump to success controller if payment is lower than minimal allowed paypal payment
            if ($total > $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                return $this->redirect($this->generateUrl('nantarena_payment_paymentprocess_paypalpreconnection', 
                    array('slug' => $event->getSlug())));
            } else {
                return $this->redirect($this->generateUrl('nantarena_payment_paypalpayment_success', array('slug' => $event->getSlug())));
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

            $total = $paypalPayment->getAmount();

            $items = array();
            foreach ($paypalPayment->getTransactions() as $transaction) {
                $name = $transaction->getUser()->getUsername() . ' - ' . $transaction->getEntry()->getEntryType()->getName();
                $item = $paypal->createItem($name, 1, $transaction->getPrice());
                array_push($items, $item);
            }

            

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

        } catch (\Exception $ex) {
            $res = $paypal->ApiErrorHandle($ex);
            if (!empty($res)) {
                $this->get('session')->getFlashBag()->add('error', 'Un problème a été renconté avec paypal (' . $res . ')');
            } else {
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
            }
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
            $this->get('session')->set('payProcess', true);

            if ($paypalPayment->getAmount() > $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                // Execution du paiement
                $paypal = $this->get('nantarena_payment.paypal_service');
                $payment = $paypal->executePayment(
                    $paypalPayment->getPaymentId(),
                    $paypalPayment->getPayerId()
                );

                $paypalPayment->setState($payment->getState());
            }


            $em = $this->getDoctrine()->getManager();
            $paypalPayment->setValid(true);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Le paiement s\'est bien déroulé - Merci');

        } catch (\Exception $ex) {
            $res = $paypal->ApiErrorHandle($ex);
            if (!empty($res)) {
                $this->get('session')->getFlashBag()->add('error', 'Un problème a été renconté avec paypal (' . $res . ')');
            } else {
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
            }
        }
        // finally {
            $this->get('session')->set('payProcess', false);
        // }

        return $this->redirect($this->generateUrl('nantarena_user_profile'));
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
            $paymentId = $paypalPayment->getPaymentId();
            $paymentStatus = $paypalPayment->getState();
            $payerId = $paypalPayment->getPayerId();
            if (!empty($paymentId)
                || !empty($paymentStatus)
                || !empty($payerId)) {
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
            $paymentId = $paypalPayment->getPaymentId();
            $paymentStatus = $paypalPayment->getState();
            $payerId = $paypalPayment->getPayerId();
            if (empty($paymentId)
                || empty($paymentStatus)
                || !empty($payerId)) {
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
            $paymentId = $paypalPayment->getPaymentId();
            $paymentStatus = $paypalPayment->getState();
            $payerId = $paypalPayment->getPayerId();
            if (empty($paymentId)
                || empty($paymentStatus)
                || empty($payerId)) {
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

        // $logger = $this->get('logger');
        // $logger->info('');
        // $logger->err('');

        $repository = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:Transaction');

        $transaction = $repository->findOneByEntry($entry);

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

        // Check session
        $session = $this->get('session');
        if ($session->has('payProcess') && $session->get('payProcess')) {
            $this->get('session')->getFlashBag()->add('error', 'Paiement en cours sur votre compte.');
            return false;
        }
        $session->set('payProcess', false);

        // Get active transaction
        $repository = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:Transaction');
        $transaction = $repository->findOneByEntry($entry);

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
        if (!$this->removePayment($payment)) {
            $this->get('session')->getFlashBag()->add('error', 'Un paiement est en cours, si cette erreur apparait encore dans 15 min, contactez un admin');
            return false;
        }
        
        $this->get('session')->getFlashBag()->add('success', 'La transaction non finie a été supprimée');
        return true;
    }


    private function removePayment(Payment $payment)
    {
        try {
            if (!$this->get('session')->get('payProcess')) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($payment);
                $em->flush();
                return true;
            }
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', 'Erreur de requête BDD');
        }
        return false;
    }
}
