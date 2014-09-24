<?php

namespace Nantarena\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Game
 *
 * @ORM\Table(name="event_game")
 * @ORM\Entity(repositoryClass="Nantarena\EventBundle\Repository\GameRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 * @UniqueEntity("name")
 */
class Game
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $platform;

    /**
     * @var int
     *
     * @ORM\Column(name="teamCapacity", type="smallint")
     * @Assert\Range(min=1, max=20)
     */
    private $teamCapacity;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

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
     * @return Event
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
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $deletedAt
     * @return $this
     */
    public function setDeletedAt(\DateTime $deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * Set platform
     *
     * @param string $platform
     * @return Game
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    
        return $this;
    }

    /**
     * Get platform
     *
     * @return string 
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set teamCapacity
     *
     * @param integer $teamCapacity
     * @return Game
     */
    public function setTeamCapacity($teamCapacity)
    {
        $this->teamCapacity = $teamCapacity;
    
        return $this;
    }

    /**
     * Get teamCapacity
     *
     * @return integer 
     */
    public function getTeamCapacity()
    {
        return $this->teamCapacity;
    }
}
