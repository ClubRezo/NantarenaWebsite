<?php

namespace Nantarena\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AdaptativePayment
 *
 * @ORM\Entity
 */
class AdaptativePayment extends Payment
{
    /**
     * @ORM\ManyToOne(targetEntity="Nantarena\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $admin;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    protected $description;

    /**
     * Set description
     *
     * @param string $description
     * @return AdaptativePayment
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
     * Set admin
     *
     * @param \Nantarena\UserBundle\Entity\User $admin
     * @return AdaptativePayment
     */
    public function setAdmin(\Nantarena\UserBundle\Entity\User $admin)
    {
        $this->admin = $admin;
    
        return $this;
    }

    /**
     * Get admin
     *
     * @return \Nantarena\UserBundle\Entity\User 
     */
    public function getAdmin()
    {
        return $this->admin;
    }
}