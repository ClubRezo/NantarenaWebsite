<?php

namespace Nantarena\PaymentBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

use Nantarena\UserBundle\Entity\User;


class Payment
{
    /**
     */
    private $partners;

    /**
     * Get partners
     *
     */
    public function getPartners()
    {
        return $this->partners;
    }

    /**
     * Set partners
     */
    public function setPartners($partnersList)
    {
        $this->partners = $partnersList;
    
        return $this;
    }
}