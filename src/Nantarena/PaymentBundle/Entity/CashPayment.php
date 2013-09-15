<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CashPayment
 *
 * @ORM\Entity
 */
class CashPayment extends Payment
{
   /**
     * @ORM\ManyToOne(targetEntity="Nantarena\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $admin;


    /**
     * Set admin
     *
     * @param \Nantarena\UserBundle\Entity\User $admin
     * @return CashPayment
     */
    public function setAdmin(\Nantarena\UserBundle\Entity\User $admin)
    {
        $this->admin = $admin;
    
        return $this;
    }

    /**
     * Get admin
     *
     * @return \Nantarena\UserBundle\Entity\User 
     */
    public function getAdmin()
    {
        return $this->admin;
    }
}