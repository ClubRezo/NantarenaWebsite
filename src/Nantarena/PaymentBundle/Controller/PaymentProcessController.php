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
     * @Route("/event/{slug}", name="nantarena_payment_paymentprocess_index")
     * @Template()
     */
    public function indexAction(Event $event, Request $request)
    {
        // check authentification
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_connection'));
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = $this->get('security.context')->getToken()->getUser();

        // get associated entry
        $entry = new Entry();
        if (!$user->hasEntry($event, $entry))
        {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_signup'));
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        // check existant transactions
        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:Transaction');

        $transaction = $repository->findOneBy(array('user' => $user->getId(), 'refund' => null)); 



        $form = $this->createForm(new PaymentType(), new PaymentModel(), array(
            'action' => $this->get('nantarena_payment.payment_manager')->Payment($event),
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) 
        {
            if($transaction)
            {
                $this->get('session')->getFlashBag()->add('error', "La transaction active doit être supprimée avant de continuer");
                return $this->redirect($this->get('nantarena_payment.payment_manager')->Payment($event));
            }

            $now = new \DateTime();

            $entity_payment = new Payment();
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
            $em->persist($entity_payment);
            $em->persist($ent_trans);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', "Une nouvelle transaction a été créée");
            return $this->redirect($this->generateUrl('nantarena_payment_paymentprocess_paymentchoice'));
        }

        return array(
            'entry' => $entry,
            'transaction' => $transaction,
            'form' => $form->createView(),
            );
    }

    /**
     * @Route("/payment-choice", name="nantarena_payment_paymentprocess_paymentchoice")
     * @Template()
     */
    public function paymentChoiceAction()
    {
        // check authentification
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_connection'));
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = $this->get('security.context')->getToken()->getUser();

        // get existant transaction
        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:Transaction');
        $transaction = $repository->findOneBy(array('user' => $user->getId(), 'refund' => null)); 
        if(!$transaction)
        {
            $this->get('session')->getFlashBag()->add('error', "Aucune transaction en cours");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        // get associated entry
        $entry = new Entry();
        if (!$user->hasEntry($transaction->getEvent(), $entry))
        {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_signup'));
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        return array(
            'entry' => $entry,
            );
    }


    /**
     * @Route("/pay", name="nantarena_payment_paypalpayment_pay")
     */
    public function payAction()
    {
        // check authentification
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_connection'));
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = $this->get('security.context')->getToken()->getUser();

        // get existant transaction
        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:Transaction');
        $transaction = $repository->findOneBy(array('user' => $user->getId(), 'refund' => null)); 
        if(!$transaction)
        {
            $this->get('session')->getFlashBag()->add('error', "Aucune transaction en cours");
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

        $event = $transaction->getEvent();

        // get associated entry
        $entry = new Entry();
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
                "Paiement de l'entré à la ".$event->getName(),
                $item_array,
                $this->get('router')->generate('nantarena_payment_paypalpayment_success', array(), true),
                $this->get('router')->generate('nantarena_payment_paypalpayment_cancel', array(), true));
        

            // ### Redirect buyer to paypal
            // Retrieve buyer approval url from the `payment` object.
            $redirectUrl = $paypal->getPaymentLink($payment);

            // Sauvegarde en bdd pour pouvoir gérer le retour

            // echo $payment->getState();
            // echo $payment->getId();

            $em = $this->getDoctrine()->getManager();
            $transaction->getPayment()->setPaymentID($payment->getId());
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
    }

     /**
     * @Route("/success", name="nantarena_payment_paypalpayment_success")
     * @Template()
     */
    public function successAction(Request $request)
    {
        // check authentification
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_connection'));
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = $this->get('security.context')->getToken()->getUser();

        // Vérification pour des possibles transaction en cours
        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:Transaction');

        $transss = $repository->findOneBy(array('user' => $user->getId(), 'refund' => null));        

        if($transss)
        {
            $this->get('session')->getFlashBag()->add('success', "Le paiement en cours a été retrouvé - et mis a jour");
            $em = $this->getDoctrine()->getManager();
            $transss->getPayment()->setPayerId($request->query->get('PayerID'));
            $em->flush();
        }



        return array(
            'trans' => $transss,
        );
    }

    /**
     * @Route("/cancel", name="nantarena_payment_paypalpayment_cancel")
     * @Template()
     */
    public function cancelAction()
    {
        // check authentification
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_connection'));
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = $this->get('security.context')->getToken()->getUser();

        // Vérification pour des possibles transaction en cours
        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:Transaction');

        $transss = $repository->findOneBy(array('user' => $user->getId(), 'refund' => null));        

        if($transss)
        {
            $this->get('session')->getFlashBag()->add('success', "Le paiment a été retrouvé - il est supprimé");
            $em = $this->getDoctrine()->getManager();
            $em->remove($transss);
            $em->remove($transss->getPayment());
            $em->flush();
        }

       return array();
    }

    /**
     * @Route("/payme", name="nantarena_payment_paypalpayment_payme")
     * @Template()
     */
    public function paymeAction()
    {
        // check authentification
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_connection'));
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = $this->get('security.context')->getToken()->getUser();

        // Vérification pour des possibles transactions en cours
        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:Transaction');

        $transss = $repository->findOneBy(array('user' => $user->getId(), 'refund' => null));        

        if($transss)
        {
            try {
                // Execution du paiement
                $paypal = $this->get('nantarena_payment.paypal_service');
                $payment = $paypal->executePayment(
                    $transss->getPayment()->getPaymentId(),
                    $transss->getPayment()->getPayerId());


                $this->get('session')->getFlashBag()->add('success', "Le paiment a été retrouvé - Vous allez banquer");
                $em = $this->getDoctrine()->getManager();
                $transss->getPayment()->setValid(True);
                $em->flush();

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


    /**
     * @Route("/clean", name="nantarena_payment_clean")
     */
    public function cleanAction()
    {
        // check authentification
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('payment.index.flash_error_connection'));
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = $this->get('security.context')->getToken()->getUser();

        // Vérification pour des possibles transactions en cours
        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:Transaction');

        $transss = $repository->findOneBy(array('user' => $user->getId(), 'refund' => null));        

        if($transss)
        {
            $this->get('session')->getFlashBag()->add('success', "Le transaction a été retrouvé - elle est supprimé");
            $em = $this->getDoctrine()->getManager();
            $em->remove($transss);
            $em->remove($transss->getPayment());
            $em->flush();
            return $this->redirect($this->generateUrl('nantarena_user_profile'));
        }

       return array();
    }


}
