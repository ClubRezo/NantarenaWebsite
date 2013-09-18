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
                    'url' => $this->generateUrl('nantarena_admin_payment_list'),
                    'role' => 'ROLE_ADMIN'
                ),
                $translator->trans('payment.admin.dashboard.transaction_management') => array(
                    'url' => $this->generateUrl('nantarena_admin_transaction_list'),
                    'role' => 'ROLE_ADMIN'
                ),
                $translator->trans('payment.admin.dashboard.paypal_management') => array(
                    'url' => $this->generateUrl('nantarena_admin_paypal_list'),
                    'role' => 'ROLE_ADMIN'
                ),
                $translator->trans('payment.admin.dashboard.refund_management') => array(
                    'url' => $this->generateUrl('nantarena_admin_refund_list'),
                    'role' => 'ROLE_ADMIN'
                ),
            )
        );
    }
}