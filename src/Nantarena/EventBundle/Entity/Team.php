<?php

namespace Nantarena\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Nantarena\EventBundle\Validator\Constraints\TeamNameConstraint;
use Nantarena\EventBundle\Validator\Constraints\TeamCreatorConstraint;
use Nantarena\EventBundle\Validator\Constraints\TeamMembersConstraint;
use Nantarena\EventBundle\Validator\Constraints\TeamMembersTournamentsConstraint;


/**
 * Team
 *
 * @ORM\Table(name="event_team")
 * @ORM\Entity(repositoryClass="Nantarena\EventBundle\Repository\TeamRepository")
 * @TeamNameConstraint(message="event.teams.unique.name")
 * @TeamCreatorConstraint(message="event.teams.creator")
 * @TeamMembersTournamentsConstraint(message="event.teams.members.tournaments")
 * @TeamMembersConstraint(
 *      emptyMessage="event.teams.members.empty",
 *      sameMessage="event.teams.members.same",
 *      alreadyTeam="event.teams.members.already"
 * )
 */
class Team
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
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
     * @ORM\Column(name="tag", type="string", length=50, nullable=true)
     */
    private $tag;

    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", nullable=true)
     * @Assert\Url()
     */
    private $logo;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", nullable=true)
     * @Assert\Url()
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $desc;

    /**
     * @ORM\OneToOne(targetEntity="Nantarena\EventBundle\Entity\Entry")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id", nullable=false)
     */
    private $creator;

    /**
     * @ORM\OneToMany(targetEntity="Nantarena\EventBundle\Entity\Entry", mappedBy="team")
     */
    private $members;

    /**
     * @ORM\ManyToOne(targetEntity="Nantarena\EventBundle\Entity\Tournament", inversedBy="teams")
     */
    private $tournament;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Team
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
     * Set tag
     *
     * @param string $tag
     * @return Team
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    
        return $this;
    }

    /**
     * Get tag
     *
     * @return string 
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return Team
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    
        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return Team
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    
        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set desc
     *
     * @param string $desc
     * @return Team
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;
    
        return $this;
    }

    /**
     * Get desc
     *
     * @return string 
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * Set creator
     *
     * @param \Nantarena\EventBundle\Entity\Entry $creator
     * @return Team
     */
    public function setCreator(\Nantarena\EventBundle\Entity\Entry $creator)
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * Get creator
     *
     * @return \Nantarena\EventBundle\Entity\Entry
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Add members
     *
     * @param \Nantarena\EventBundle\Entity\Entry $members
     * @return Team
     */
    public function addMember($members)
    {
        if (null !== $members) {
            $this->members[] = $members;
        }

        return $this;
    }

    /**
     * Remove members
     *
     * @param \Nantarena\EventBundle\Entity\Entry $members
     */
    public function removeMember(\Nantarena\EventBundle\Entity\Entry $members)
    {
        $members->setTeam(null);
        $this->members->removeElement($members);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * get tournament
     *
     * @return mixed
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * Set tournament
     *
     * @param mixed $tournament
     * @return Team
     */
    public function setTournament(\Nantarena\EventBundle\Entity\Tournament $tournament)
    {
        $this->tournament = $tournament;

        return $this;
    }
}
