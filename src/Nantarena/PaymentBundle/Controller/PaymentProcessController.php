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

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

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

        // Get active transaction
        $repository = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:Transaction');

        // Get partners
        $userList = array();
        $userLabel = array();
        // Get all user teams
        foreach ($user->getTeams() as $team) {
            // in the current event
            if ($team->getEvent() === $event) {
                // for each person
                foreach ($team->getMembers() as $member) {
                    if ($member !== $user) {
                        if (!in_array($member, $userList)) {
                            // Get entry
                            $partnerEntry = null;
                            $member->hasEntry($event, $partnerEntry);

                            if ($partnerEntry) {
                                // Check payment
                                $validTransaction = $repository->findValidPayment($partnerEntry);
                                if (!$validTransaction) {
                                    $label = $member->getUsername() . ' - ' . $partnerEntry->getEntryType()->getName() . ' - ' . $partnerEntry->getEntryType()->getPrice() . '€';
                                    array_push($userList, $member);
                                    array_push($userLabel, $label);
                                }
                            }
                        }
                    }
                }
            }
        }

        // Create the choice list interface
        if (count($userList) > 0) {
            $userAndLabel = new ChoiceList($userList, $userLabel, array());
            $isPartner = true;
        } else {
            $userAndLabel = null;
            $isPartner = false;
        }

        $model = new PaymentModel();
        $form = $this->createForm(new PaymentType(), $model, array(
            'action' => $this->get('nantarena_payment.payment_manager')->createPayment($event),
            'method' => 'POST',
            'partnerList' => $userAndLabel
        ));

        $form->handleRequest($request);


        if ($form->isValid()) {

            // Payment and transaction creation
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
            $listPartners = $model->getPartners();
            if (!empty($listPartners)) {
                foreach ($model->getPartners() as $partner) {
                    $entry = null;
                    $partner->hasEntry($event, $entry);
                    if ($entry) {
                        $transaction = new Transaction();
                        $transaction
                            ->setPrice($entry->getEntryType()->getPrice())
                            ->setUser($partner)
                            ->setEvent($event)
                            ->setPayment($paypalPayment)
                        ;
                        array_push($allTransactions, $transaction);
                    }
                }
            }
            
            // jump paypal system if the amout is too low
            $total = 0.00;
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

            // Push in Database
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
                    $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.base_error'));
                    return $this->redirect($this->generateUrl('nantarena_user_profile'));
                }
            }

            // New transaction have been created

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
            'is_partner' => $isPartner,
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
        $user->hasEntry($event, $entry);

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
                $event->getName(),
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

                if (!empty($redirectUrl)) {
                    return $this->redirect($redirectUrl);
                } else {
                    $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.base_error'));
                    return $this->redirect($this->generateUrl('nantarena_user_profile'));
                }

            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.base_error'));
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }

        } catch (\Exception $ex) {
            $res = $paypal->ApiErrorHandle($ex);
            if (!empty($res)) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.paypal'));
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
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.base_error'));
                return $this->redirect($this->generateUrl('nantarena_user_profile'));
            }

            return $this->redirect($this->generateUrl('nantarena_payment_paypalpayment_success', array('slug' => $event->getSlug())));
        } elseif ($state === 'cancel') {
            
            $this->removePayment($paypalPayment);

            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.cancel'));
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        } else {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.base_error'));
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
            'payment' => $transaction->getPayment()
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

            $this->get('logger')->info('PAYPAL : ' . $this->getUser()->getUsername() . ' pays ' . strval($transaction->getPayment()->getAmount()) . '€ for ' . strval(count($transaction->getPayment()->getTransactions())) . ' people');

            $em = $this->getDoctrine()->getManager();
            $paypalPayment->setValid(true);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('payment.payment_process.message.payment'));

        } catch (\Exception $ex) {
            $res = $paypal->ApiErrorHandle($ex);
            if (!empty($res)) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.paypal'));
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

            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.cancel'));
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
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.bad_step'));
                return null;
            } elseif ($paypalPayment->getAmount() <= $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.small_amount'));
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
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.bad_step'));
                return null;
            } elseif ($paypalPayment->getAmount() <= $this->container->getParameter('nantarena_payment.payment_min_euro')) {
                 $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.small_amount'));
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
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.bad_step'));
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
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.event_signup'));
            return null;
        }

        $repository = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:Transaction');
        $transaction = $repository->findOneByEntry($entry);

        if (!$transaction) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.base_error'));
            return null;
        }

        $payment = $transaction->getPayment();

        if ($payment->isValid()) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.payed'));
            return null;
        }

        if (!$payment instanceof PaypalPayment) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.pay_admin'));
            return null;
        }

        if ($payment->getUser() !== $user) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.coop', array('%user%' => $payment->getUser()->getUsername())));
            return null;
        }

        // check timeout
        $minTime = $this->container->getParameter('nantarena_payment.payment_timeout');
        $date = new \DateTime();
        $date->modify('-'.$minTime.' min');
        if ($date > $payment->getDate()) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.expired'));

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
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.base_error'));
            return null;
        }

        // Manage session
        $this->get('session')->set('payProcess', false);
        
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
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.event_signup'));
            return false;
        }

        // Check session
        $session = $this->get('session');
        if ($session->has('payProcess') && $session->get('payProcess')) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.pay_running'));
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
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.payed'));
            return false;
        }

        if (!$payment instanceof PaypalPayment) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.pay_admin'));
            return false;
        }

        if ($payment->getUser() !== $user) {

            $minTime = $this->container->getParameter('nantarena_payment.payment_timeout');
            $securityTime = $this->container->getParameter('nantarena_payment.payment_delete_security_time');
            $totalTime = $minTime + $securityTime;

            $date = new \DateTime();
            $date->modify('-'.$totalTime.' min');
            $interval = $payment->getDate()->diff($date);
            $endDateMin = $interval->format('%i');
            $endDateSec = $interval->format('%s');

            if ($date < $payment->getDate()) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.coop', array('%user%' => $payment->getUser()->getUsername())));
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.time', array('%min%' => $endDateMin, '%sec%' => $endDateSec)));

                return false;
            }
        }

        // clean transaction
        if (!$this->removePayment($payment)) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.time_error'));
            return false;
        }
        
        // Old transaction have been deleted
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
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.payment_process.message.base_error'));
        }
        return false;
    }
}
