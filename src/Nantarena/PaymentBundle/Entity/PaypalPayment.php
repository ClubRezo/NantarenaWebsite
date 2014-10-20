<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * PaypalPayment
 *
 * @ORM\Entity
 */
class PaypalPayment extends Payment
{
    /**
     * @var string
     *
     * @ORM\Column(name="paypal_paymentid", type="string", length=100, nullable=true)
     */
    private $paymentId;

    /**
     * @var string
     *
     * @ORM\Column(name="paypal_payerid", type="string", length=100, nullable=true)
     */
    private $payerId;

    /**
     * @var string
     *
     * @ORM\Column(name="paypal_state", type="string", length=100, nullable=true)
     */
    private $state;



    /**
     * Set paymentId
     *
     * @param string $paymentId
     * @return PaypalPayment
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
     * @return PaypalPayment
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
     * Set state
     *
     * @param string $state
     * @return PaypalPayment
     */
    public function setState($state)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }
}