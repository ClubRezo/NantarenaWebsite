<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Transaction
 *
 * @ORM\Table(name="payment_transaction", uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"user_id", "event_id", "refund_id"})}
 * )
 * @ORM\Entity(repositoryClass="Nantarena\PaymentBundle\Repository\TransactionRepository")
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
     * @ORM\ManyToOne(targetEntity="Nantarena\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
    * @ORM\ManyToOne(
    *   targetEntity="Nantarena\EventBundle\Entity\Event")
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
    *   inversedBy="transactions")
    * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
    */
    private $payment;

    /**
    * @ORM\ManyToOne(
    *   targetEntity="Nantarena\PaymentBundle\Entity\Refund",
    *   inversedBy="transactions")
    * @ORM\JoinColumn(name="refund_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
    */
    private $refund;

    /**
     * @ORM\ManyToOne(
     *   targetEntity="Nantarena\PaymentBundle\Entity\GlobalCoupon", inversedBy="transactions")
     */
    private $globalCoupon;

    /**
     * @ORM\OneToOne(
     *   targetEntity="Nantarena\PaymentBundle\Entity\UniqueCoupon", inversedBy="transaction")
     */
    private $uniqueCoupon;

    /**
     * Is refund
     *
     * @return boolean 
     */
    public function isRefund()
    {
        $refund = $this->refund;
        if (!empty($refund)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * isValidRefund
     *
     * @return boolean 
     */
    public function isValidRefund()
    {
        $refund = $this->refund;
        if (!empty($refund) and $refund->isValid()) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * getEntry
     *
     * @return boolean 
     */
    public function getEntry()
    {
        $entry = null;

        $this->user->hasEntry($this->event, $entry);

        return $entry;
    }

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
        $this->price = round($price, 2);
    
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

    /**
     * @return mixed
     */
    public function getUniqueCoupon()
    {
        return $this->uniqueCoupon;
    }

    /**
     * @param mixed $uniqueCoupon
     */
    public function setUniqueCoupon($uniqueCoupon)
    {
        $this->uniqueCoupon = $uniqueCoupon;
    }

    /**
     * @return mixed
     */
    public function getGlobalCoupon()
    {
        return $this->globalCoupon;
    }

    /**
     * @param mixed $globalCoupon
     */
    public function setGlobalCoupon($globalCoupon)
    {
        $this->globalCoupon = $globalCoupon;
    }


}
