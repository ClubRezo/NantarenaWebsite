<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

 // * @ORM\InheritanceType("SINGLE_TABLE")
 // * @ORM\DiscriminatorColumn(name="method", type="string")
 // * @ORM\DiscriminatorMap({"classic" = "Payment", "paypal" = "PaypalPayment"})

/**
 * Payment
 *
 * @ORM\Entity
 * @ORM\Table(name="payment_payment")
 */
class Payment
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Nantarena\UserBundle\Entity\User",
     *      inversedBy="entries")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(name="price", type="decimal", precision=5, scale=2)
     * @Assert\GreaterThanOrEqual(value=0)
     */
    private $amount;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valid;

    /**
     * @var string
     *
     * @ORM\Column(name="paymentid", type="string", length=100, nullable=true)
     */
    private $paymentId;

    /**
     * @var string
     *
     * @ORM\Column(name="payerid", type="string", length=100, nullable=true)
     */
    private $payerId;



 

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Payment
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }

    /**
     * Get amount
     *
     * @return string 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set valid
     *
     * @param boolean $valid
     * @return Payment
     */
    public function setValid($valid)
    {
        $this->valid = $valid;
    
        return $this;
    }

    /**
     * Get valid
     *
     * @return boolean 
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Set paymentId
     *
     * @param string $paymentId
     * @return Payment
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    
        return $this;
    }

    /**
     * Get paymentId
     *
     * @return string 
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Set payerId
     *
     * @param string $payerId
     * @return Payment
     */
    public function setPayerId($payerId)
    {
        $this->payerId = $payerId;
    
        return $this;
    }

    /**
     * Get payerId
     *
     * @return string 
     */
    public function getPayerId()
    {
        return $this->payerId;
    }

    /**
     * Set user
     *
     * @param \Nantarena\UserBundle\Entity\User $user
     * @return Payment
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
}