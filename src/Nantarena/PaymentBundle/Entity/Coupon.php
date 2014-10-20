<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
 
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Coupon
 *
 * @ORM\Entity(repositoryClass="Nantarena\PaymentBundle\Repository\CouponRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"unique" = "UniqueCoupon", "global" = "GlobalCoupon"})
 * @ORM\Table(name="payment_coupon")
 */
class Coupon
{
    const TYPE_PERCENT = 1;
    const TYPE_MINUS = 2;
    const TYPE_PLUS = 4;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200, unique=true)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @ORM\Column(name="op_type", type="decimal")
     */
    private $op_type;

    /**
     * @ORM\Column(name="op_value", type="decimal", precision=5, scale=2)
     */
    private $op_value;

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
     * Set name
     *
     * @param string $name
     * @return Coupon
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
     * @return Coupon
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

    /**
     * Set op_type
     *
     * @param string $opType
     * @return Coupon
     */
    public function setOpType($opType)
    {
        if ($opType === self::TYPE_PERCENT || $opType === self::TYPE_MINUS || $opType === self::TYPE_PLUS) {
            $this->op_type = $opType;
        }

        return $this;
    }

    /**
     * Get op_type
     *
     * @return string 
     */
    public function getOpType()
    {
        return $this->op_type;
    }

    /**
     * Set op_value
     *
     * @param string $opValue
     * @return Coupon
     */
    public function setOpValue($opValue)
    {
        $this->op_value = $opValue;
    
        return $this;
    }

    /**
     * Get op_value
     *
     * @return string 
     */
    public function getOpValue()
    {
        return $this->op_value;
    }

    public function isUnique() {
        return $this instanceof UniqueCoupon;
    }

    public function isGlobal() {
        return $this instanceof GlobalCoupon;
    }

    public function getOperation() {
        if ($this->getOpType() === self::TYPE_PERCENT) {
            return '-' . $this->getOpValue() . '%';
        } else if ($this->getOpType() === self::TYPE_MINUS) {
            return '-' . $this->getOpValue() . '€';
        } else if ($this->getOpType() === self::TYPE_PLUS) {
            return '+' . $this->getOpValue() . '€';
        } else {
            return '-';
        }
    }
}
