<?php

namespace Nantarena\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Tournament
 *
 * @ORM\Table(name="event_tournament")
 * @ORM\Entity()
 */
class Tournament
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
    *   targetEntity="Nantarena\EventBundle\Entity\Event",
    *   inversedBy="tournaments")
    * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
    */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="Nantarena\EventBundle\Entity\Game")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id", nullable=false)
     */
    private $game;

    /**
     * @ORM\ManyToOne(targetEntity="Nantarena\UserBundle\Entity\User")
     */
    private $admin;

    /**
     * @var int
     *
     * @ORM\Column(name="max_teams", type="integer")
     * @Assert\GreaterThanOrEqual(value=2)
     */
    private $maxTeams;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime")
     * @Assert\DateTime()
     */
    private $startDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="exclusive", type="boolean")
     * @Assert\Type(type="boolean")
     */
    private $exclusive;


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
     * Set maxTeams
     *
     * @param integer $maxTeams
     * @return Tournament
     */
    public function setMaxTeams($maxTeams)
    {
        $this->maxTeams = $maxTeams;
    
        return $this;
    }

    /**
     * Get maxTeams
     *
     * @return integer 
     */
    public function getMaxTeams()
    {
        return $this->maxTeams;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Tournament
     */
    public function setStartDate(\DateTime $startDate)
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
     * Set event
     *
     * @param \Nantarena\EventBundle\Entity\Event $event
     * @return Tournament
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
     * Set game
     *
     * @param \Nantarena\EventBundle\Entity\Game $game
     * @return Tournament
     */
    public function setGame(\Nantarena\EventBundle\Entity\Game $game)
    {
        $this->game = $game;
    
        return $this;
    }

    /**
     * Get game
     *
     * @return \Nantarena\EventBundle\Entity\Game 
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * Set admin
     *
     * @param \Nantarena\UserBundle\Entity\User $admin
     * @return Tournament
     */
    public function setAdmin(\Nantarena\UserBundle\Entity\User $admin = null)
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

    /**
     * Set exclusive
     *
     * @param boolean $exclusive
     * @return Tournament
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;
    
        return $this;
    }

    /**
     * Get exclusive
     *
     * @return boolean 
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }
}