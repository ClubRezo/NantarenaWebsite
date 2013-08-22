<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * Payment
 *
 * @ORM\Entity
 */
// class PaypalPayment extends Payment
// {
//     /**
//      * @var string
//      *
//      * @ORM\Column(name="ident", type="string", length=100)
//      */
//     private $ident;

//     /**
//      * @var string
//      *
//      * @ORM\Column(name="state", type="string", length=100)
//      */
//     private $state;

//     /**
//      * Set ident
//      *
//      * @param string $ident
//      * @return PaypalPayment
//      */
//     public function setIdent($ident)
//     {
//         $this->ident = $ident;
    
//         return $this;
//     }

//     /**
//      * Get ident
//      *
//      * @return string 
//      */
//     public function getIdent()
//     {
//         return $this->ident;
//     }

//     /**
//      * Set state
//      *
//      * @param string $state
//      * @return PaypalPayment
//      */
//     public function setState($state)
//     {
//         $this->state = $state;
    
//         return $this;
//     }

//     /**
//      * Get state
//      *
//      * @return string 
//      */
//     public function getState()
//     {
//         return $this->state;
//     }
// }