<?php

namespace Nantarena\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entry
 *
 * @ORM\Table(name="event_entry")
 * @ORM\Entity(repositoryClass="Nantarena\EventBundle\Repository\EntryRepository")
 */
class Entry
{
    /**
    * @ORM\Id()
    * @ORM\ManyToOne(
    *   targetEntity="Nantarena\EventBundle\Entity\Tournament")
    * @ORM\JoinColumn(name="event_tournament_id", referencedColumnName="id", nullable=false)
    */
    private $tournament;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(
     *      targetEntity="Nantarena\UserBundle\Entity\User",
     *      inversedBy="entries")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Nantarena\EventBundle\Entity\Team")
     */
    private $team;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registration_date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $registrationDate;

    /**
     * Set registrationDate
     *
     * @param \DateTime $registrationDate
     * @return Entry
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;
    
        return $this;
    }

    /**
     * Get registrationDate
     *
     * @return \DateTime 
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * Set tournament
     *
     * @param \Nantarena\EventBundle\Entity\Tournament $tournament
     * @return Entry
     */
    public function setTournament(\Nantarena\EventBundle\Entity\Tournament $tournament)
    {
        $this->tournament = $tournament;
    
        return $this;
    }

    /**
     * Get tournament
     *
     * @return \Nantarena\EventBundle\Entity\Tournament
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * Set user
     *
     * @param \Nantarena\UserBundle\Entity\User $user
     * @return Entry
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
     * get optional team
     *
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set optional team
     *
     * @param mixed $team
     * @return Entry
     */
    public function setTeam(\Nantarena\EventBundle\Entity\Team $team)
    {
        $this->team = $team;

        return $this;
    }


}
