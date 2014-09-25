<?php

namespace Nantarena\PaymentBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Manage routing
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
// Request for form
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

// Entity
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Entity\Entry;

use Nantarena\PaymentBundle\Entity\Payment;
use Nantarena\PaymentBundle\Entity\CashPayment;

use Nantarena\UserBundle\Entity\User;


/**
 * Class CouponsController
 *
 * @package Nantarena\PaymentBundle\Controller\Admin
 *
 * @Route("/admin/payment/coupons")
 */
class CouponsController extends Controller
{
    /**
     * @Route("/list", name="nantarena_admin_payment_coupons_list")
     * @Template()
     */
    public function listAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('NantarenaPaymentBundle:Coupon');
        $coupons = $repository->findAll();

        return array(
            'coupons' => $coupons
        );
    }
}
