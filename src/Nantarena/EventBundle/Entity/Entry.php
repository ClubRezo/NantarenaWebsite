<?php

namespace Nantarena\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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
    *   targetEntity="Nantarena\EventBundle\Entity\EntryType")
    * @ORM\JoinColumn(name="event_entry_type_id", referencedColumnName="id", nullable=false)
    */
    private $entryType;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(
     *      targetEntity="Nantarena\UserBundle\Entity\User",
     *      inversedBy="entries")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

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
     * Set entryType
     *
     * @param \Nantarena\EventBundle\Entity\EntryType $entryType
     * @return Entry
     */
    public function setEntryType(\Nantarena\EventBundle\Entity\EntryType $entryType)
    {
        $this->entryType = $entryType;
    
        return $this;
    }

    /**
     * Get entryType
     *
     * @return \Nantarena\EventBundle\Entity\EntryType
     */
    public function getEntryType()
    {
        return $this->entryType;
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeams()
    {
        if (null !== $this->user) {
            $teams = $this->getUser()->getTeams();

            /** @var Team $team */
            foreach ($teams as $key => $team) {
                if ($team->getEvent() !== $this->getEntryType()->getEvent())
                    unset($teams[$key]);
            }

            return $teams;
        }

        return null;
    }

    public function getTournaments()
    {
        $teams = $this->getTeams();
        $tournaments = new ArrayCollection();

        /** @var Team $team */
        foreach ($teams as $team) {
            foreach($team->getTournaments() as $tournament) {
                $tournaments->add($tournament);
            }
        }

        return $tournaments;
    }
}
