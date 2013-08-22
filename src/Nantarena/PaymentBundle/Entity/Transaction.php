<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Transaction
 *
 * @ORM\Table(name="payment_transaction")
 * @ORM\Entity
 */
class Transaction
{
    /**
     * @var integer
     *
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
    * @ORM\ManyToOne(
    *   targetEntity="Nantarena\EventBundle\Entity\Event",
    *   inversedBy="entryTypes")
    * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
    */
    private $event;

    /**
     * @ORM\Column(name="price", type="decimal", precision=5, scale=2)
     * @Assert\GreaterThanOrEqual(value=0)
     */
    private $price;

    /**
    * @ORM\ManyToOne(
    *   targetEntity="Nantarena\PaymentBundle\Entity\Payment",
    *   inversedBy="entryTypes")
    * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=false)
    */
    private $payment;

    /**
    * @ORM\ManyToOne(
    *   targetEntity="Nantarena\PaymentBundle\Entity\Refund",
    *   inversedBy="entryTypes")
    * @ORM\JoinColumn(name="refund_id", referencedColumnName="id", nullable=true)
    */
    private $refund;
    

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
     * Set price
     *
     * @param string $price
     * @return Transaction
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return string 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set user
     *
     * @param \Nantarena\UserBundle\Entity\User $user
     * @return Transaction
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
     * Set event
     *
     * @param \Nantarena\EventBundle\Entity\Event $event
     * @return Transaction
     */
    public function setEvent(\Nantarena\EventBundle\Entity\Event $event)
    {
        $this->event = $event;
    
        return $this;
    }

    /**
     * Get event
     *
     * @return \Nantarena\EventBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set payment
     *
     * @param \Nantarena\PaymentBundle\Entity\Payment $payment
     * @return Transaction
     */
    public function setPayment(\Nantarena\PaymentBundle\Entity\Payment $payment)
    {
        $this->payment = $payment;
    
        return $this;
    }

    /**
     * Get payment
     *
     * @return \Nantarena\PaymentBundle\Entity\Payment 
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Set refund
     *
     * @param \Nantarena\PaymentBundle\Entity\Refund $refund
     * @return Transaction
     */
    public function setRefund(\Nantarena\PaymentBundle\Entity\Refund $refund = null)
    {
        $this->refund = $refund;
    
        return $this;
    }

    /**
     * Get refund
     *
     * @return \Nantarena\PaymentBundle\Entity\Refund 
     */
    public function getRefund()
    {
        return $this->refund;
    }
}