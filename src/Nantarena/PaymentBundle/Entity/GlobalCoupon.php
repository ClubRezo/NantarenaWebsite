<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * globalCoupon
 *
 * @ORM\Entity
 */
class GlobalCoupon extends Coupon
{
    /**
     * @ORM\OneToMany(targetEntity="Nantarena\PaymentBundle\Entity\Transaction", mappedBy="globalCoupon")
     */
    private $transactions;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valid;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
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

    /**
     * Set valid
     *
     * @param boolean $valid
     * @return Coupon
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
}
