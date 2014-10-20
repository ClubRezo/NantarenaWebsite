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
use Nantarena\PaymentBundle\Entity\CashPayment;

use Nantarena\UserBundle\Entity\User;


/**
 * Class CashController
 *
 * @package Nantarena\PaymentBundle\Controller\Admin
 *
 * @Route("/admin/payment/cash")
 */
class CashController extends Controller
{
    /**
     * @Route("/user/{id}", name="nantarena_admin_payment_cash_user")
     * @Template()
     */
    public function payUserAction(User $user)
    {
        return array(
        	'user' => $user,
        );
    }
}