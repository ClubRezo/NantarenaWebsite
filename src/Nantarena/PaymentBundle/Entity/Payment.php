<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
 
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Payment
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="method", type="string")
 * @ORM\DiscriminatorMap({"classic" = "Payment", "paypal" = "PaypalPayment"})
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
     * @ORM\ManyToOne(targetEntity="Nantarena\UserBundle\Entity\User")
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
     * @ORM\OneToMany(targetEntity="Nantarena\PaymentBundle\Entity\Transaction", mappedBy="payment", cascade={"remove"})
     */
    private $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
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

    /**
     * Add transactions
     *
     * @param \Nantarena\PaymentBundle\Entity\Transaction $transactions
     * @return Payment
     */
    public function addTransaction(\Nantarena\PaymentBundle\Entity\Transaction $transactions)
    {
        $this->transactions[] = $transactions;
    
        return $this;
    }

    /**
     * Remove transactions
     *
     * @param \Nantarena\PaymentBundle\Entity\Transaction $transactions
     */
    public function removeTransaction(\Nantarena\PaymentBundle\Entity\Transaction $transactions)
    {
        $this->transactions->removeElement($transactions);
    }

    /**
     * Get transactions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTransactions()
    {
        return $this->transactions;
    }
}