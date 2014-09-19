<?php
// src/Nantarena/SiteBundle/Countdown/CountdownListener.php

namespace Nantarena\SiteBundle\Countdown;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CountdownListener
{

    protected $countdownRegistration; // processeur


    // - Avant cette date, on affichera un compte à rebours
    // - Après cette date, on n'affichera plus le « bêta »

    protected $endDate;

    public function __construct(CountdownRegistration $countdownRegistration, $endDate)
    {
        $this->countdownRegistration = $countdownRegistration;
        $this->endDate  = new \Datetime($endDate);
    }

    public function processCountdown(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $remainingDays = (new \Datetime())->diff($this->endDate)->format("%r%a");

        if ($remainingDays <= 0) {
            return; // Si date dépassée, on ne fait rien
        }

        $response = $this->countdownRegistration->displayCountdown($event->getResponse(), $remainingDays);
        $event->setResponse($response);
    }
}