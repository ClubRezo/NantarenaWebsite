<?php

namespace Nantarena\PaymentBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Manage routing
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
// Request for form
use Symfony\Component\HttpFoundation\Request;

// Entity
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Entity\Entry;

use Nantarena\PaymentBundle\Entity\PaypalPayment;

/**
 * Class PaypalController
 *
 * @package Nantarena\PaymentBundle\Controller\Admin
 *
 * @Route("/admin/paypal")
 */
class PaypalController extends Controller
{
    /**
     * @Route("/list", name="nantarena_admin_paypal_list")
     * @Template()
     */
    public function listAction()
    {
        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:PaypalPayment');
        $lpaypal = $repository->findBy(
            array('valid' => false)
        );

        return array('lpaypal' => $lpaypal);
    }

    /**
     * @Route("/clean-outdated", name="nantarena_admin_paypal_clean")
     */
    public function cleanOutdatedAction()
    {
        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:PaypalPayment');
        $lpaypal = $repository->findBy(
            array('valid' => false)
        );

        $time_min = $this->container->getParameter('nantarena_payment.payment_timeout');
        $time_secu = $this->container->getParameter('nantarena_payment.payment_delete_security_time');
        $time_tot = $time_min + $time_secu;

        $date = new \DateTime();
        $date->modify('-'.$time_tot.' min');

        $em = $this->getDoctrine()->getManager();
        $count = 0;
        foreach ($lpaypal as $paypal) {
            if ($date >= $paypal->getDate()) {
                $count++;
                $em->remove($lpaypal);
            }
        }
        $em->flush();

        if ($count > 0) {
            $this->get('session')->getFlashBag()->add('success', $count.' paiement(s) ont été nettoyé(s)');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Aucun paiement n a été supprimé');
        }
        
        return $this->redirect($this->generateUrl('nantarena_admin_paypal_list'));
    }

}