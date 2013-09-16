<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * uniqCoupon
 *
 * @ORM\Entity
 * @ORM\Table(name="payment_uniqcoupon")
 */
class UniqCoupon extends Coupon
{
   /**
     * @ORM\OneToOne(targetEntity="Nantarena\PaymentBundle\Entity\Transaction")
     * @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=false)
     */
    private $transaction;

    /**
     * Set transaction
     *
     * @param \Nantarena\PaymentBundle\Entity\Transaction $transaction
     * @return uniqCoupon
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
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;


    /**
     * Set name
     *
     * @param string $name
     * @return uniqCoupon
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return uniqCoupon
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
}