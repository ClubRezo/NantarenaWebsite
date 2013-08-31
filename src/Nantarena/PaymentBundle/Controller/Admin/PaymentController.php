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

use Nantarena\PaymentBundle\Entity\Payment;
use Nantarena\PaymentBundle\Entity\PaypalPayment;


/**
 * Class PaymentController
 *
 * @package Nantarena\PaymentBundle\Controller\Admin
 *
 * @Route("/admin/payment")
 */
class PaymentController extends Controller
{
    /**
     * @Route("/list", name="nantarena_admin_payment_list")
     * @Template()
     */
    public function listAction()
    {
        $repository = $this->getDoctrine()
            ->getRepository('NantarenaPaymentBundle:Payment');
        $lpayment = $repository->findBy(
            array('valid' => true)
        );
        // TODO get by event only

        $time_min = $this->container->getParameter('nantarena_payment.payment_timeout');

        return array('lpayment' => $lpayment);
    }

     /**
     * @Route("/details/{id}", name="nantarena_admin_payment_details")
     * @Template()
     */
    public function detailsAction(Payment $payment)
    {
        return array(
            'payment' => $payment
        );
    }
}