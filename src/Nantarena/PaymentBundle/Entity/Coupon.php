<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
 
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Coupon
 *
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"coupon" = "Coupon", "uniq" = "UniqCoupon", "global" = "GlobalCoupon"})
 * @ORM\Table(name="payment_coupon")
 */
class Coupon
{
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
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="Nantarena\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $assocDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valid;

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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Coupon
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    
        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Coupon
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    
        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set assocDate
     *
     * @param \DateTime $assocDate
     * @return Coupon
     */
    public function setAssocDate($assocDate)
    {
        $this->assocDate = $assocDate;
    
        return $this;
    }

    /**
     * Get assocDate
     *
     * @return \DateTime 
     */
    public function getAssocDate()
    {
        return $this->assocDate;
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

    /**
     * Set op_type
     *
     * @param string $opType
     * @return Coupon
     */
    public function setOpType($opType)
    {
        $this->op_type = $opType;
    
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
}