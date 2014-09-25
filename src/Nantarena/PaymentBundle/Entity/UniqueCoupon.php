<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UniqueCoupon
 *
 * @ORM\Entity
 */
class UniqueCoupon extends Coupon
{
   /**
     * @ORM\OneToOne(targetEntity="Nantarena\PaymentBundle\Entity\Transaction", mappedBy="uniqueCoupon")
     */
    private $transaction;

    /**
     * @ORM\ManyToOne(targetEntity="Nantarena\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $code;

    /**
     * Set transaction
     *
     * @param \Nantarena\PaymentBundle\Entity\Transaction $transaction
     * @return UniqueCoupon
     */
    public function setTransaction(\Nantarena\PaymentBundle\Entity\Transaction $transaction)
    {
        $this->transaction = $transaction;
    
        return $this;
    }

    /**
     * Get transaction
     *
     * @return \Nantarena\PaymentBundle\Entity\Transaction 
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set user
     *
     * @param \Nantarena\UserBundle\Entity\User $user
     * @return Coupon
     */
    public function setUser(\Nantarena\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Nantarena\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
}
