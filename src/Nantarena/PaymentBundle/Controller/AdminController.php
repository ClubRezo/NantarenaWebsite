<?php

namespace Nantarena\PaymentBundle\Controller;

use Nantarena\AdminBundle\Controller\DashboardInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller implements DashboardInterface
{
    public function dashboardAction()
    {
        $translator = $this->get('translator');

        return array(
            'module_title' => $translator->trans('payment.admin.dashboard.title'),
            'module_links' => array(
                $translator->trans('payment.admin.dashboard.payment_management') => array(
                    'url' => $this->generateUrl('nantarena_admin_payment_payment_list'),
                    'role' => 'ROLE_PAYMENT_ADMIN'
                ),
                $translator->trans('payment.admin.dashboard.transaction_management') => array(
                    'url' => $this->generateUrl('nantarena_admin_payment_transaction_list'),
                    'role' => 'ROLE_PAYMENT_ADMIN'
                ),
                $translator->trans('payment.admin.dashboard.paypal_management') => array(
                    'url' => $this->generateUrl('nantarena_admin_payment_paypal_list'),
                    'role' => 'ROLE_PAYMENT_ADMIN'
                ),
                $translator->trans('payment.admin.dashboard.refund_management') => array(
                    'url' => $this->generateUrl('nantarena_admin_payment_refund_list'),
                    'role' => 'ROLE_PAYMENT_ADMIN'
                ),
                $translator->trans('payment.admin.dashboard.coupons_management') => array(
                    'url' => $this->generateUrl('nantarena_admin_payment_coupons_list'),
                    'role' => 'ROLE_PAYMENT_ADMIN'
                ),
            )
        );
    }
}
