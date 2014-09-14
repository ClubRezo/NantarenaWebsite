<?php

namespace Nantarena\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;
use Nantarena\EventBundle\Entity\Entry;
use Nantarena\EventBundle\Entity\Event;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Nantarena\UserBundle\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Nantarena\UserBundle\Entity\Group", inversedBy="users")
     */
    protected $groups;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="registration_date")
     * @Gedmo\Timestampable(on="create")
     */
    protected $registrationDate;

    /**
     * @var string
     * @ORM\Column(type="string", name="firstname", nullable=true)
     * @Assert\NotBlank(groups={"identity"})
     */
    protected $firstname;

    /**
     * @var string
     * @ORM\Column(type="string", name="lastname", nullable=true)
     * @Assert\NotBlank(groups={"identity"})
     */
    protected $lastname;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="birthdate", nullable=true)
     * @Assert\DateTime(groups={"identity"})
     * @Assert\NotBlank(groups={"identity"})
     */
    protected $birthdate;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Nantarena\EventBundle\Entity\Entry",
     *      mappedBy="user",
     *      cascade={"persist", "remove"})
     */
    protected $entries;

    /**
     * @ORM\ManyToMany(targetEntity="Nantarena\EventBundle\Entity\Team", mappedBy="members")
     */
    private $teams;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->groups = new ArrayCollection();
        $this->teams = new ArrayCollection();
    }

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
     * Set registrationDate
     *
     * @param \DateTime $registrationDate
     * @return User
     */
    public function setRegistrationDate(\DateTime $registrationDate)
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
     * Add groups
     *
     * @param GroupInterface $groups
     * @return User
     */
    public function addGroup(GroupInterface $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param GroupInterface $groups
     * @return User
     */
    public function removeGroup(GroupInterface $groups)
    {
        $this->groups->removeElement($groups);
        return $this;
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    
        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    
        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set birthdate
     *
     * @param \DateTime $birthdate
     * @return User
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    
        return $this;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime 
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Add entries
     *
     * @param \Nantarena\EventBundle\Entity\Entry $entries
     * @return User
     */
    public function addEntry(\Nantarena\EventBundle\Entity\Entry $entries)
    {
        $entries->setUser($this);
        $this->entries[] = $entries;
    
        return $this;
    }

    /**
     * Remove entries
     *
     * @param \Nantarena\EventBundle\Entity\Entry $entries
     */
    public function removeEntry(\Nantarena\EventBundle\Entity\Entry $entries)
    {
        $this->entries->removeElement($entries);
    }

    /**
     * Get entries
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Check if user participate to an event
     *
     * @param Event $event
     * @param Entry $entryResult
     * @return bool
     */
    public function hasEntry(Event $event, Entry &$entryResult = null)
    {
        $entries = $this->getEntries();
        $result = false;

        /** @var \Nantarena\EventBundle\Entity\Entry $entry */
        foreach($entries as $entry) {
            if ($entry->getTournament()->getEvent() === $event) {
                $result = true;
                $entryResult = $entry;
                break;
            }
        }

        return $result;
    }

    public function hasRole($role)
    {
        /** @var Group $group */
        foreach ($this->groups as $group) {
            foreach ($group->getRoles() as $groupRole) {
                if ($role === $groupRole) {
                    return true;
                }
            }
        }

        return parent::hasRole($role);
    }

    /**
     * Get teams
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTeams()
    {
        return $this->teams;
    }
}
