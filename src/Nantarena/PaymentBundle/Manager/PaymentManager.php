<?php

namespace Nantarena\PaymentBundle\Manager;

use Nantarena\PaymentBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

use Nantarena\EventBundle\Entity\Event;

/**
 * Class CategoryManager
 *
 * @package Nantarena\PaymentBundle\Manager
 */
class PaymentManager
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function Payment(Event $event)
    {
        return $this->router->generate('nantarena_payment_paymentprocess_index', array(
            'slug' => $event->getSlug(),
        ));
    }
}