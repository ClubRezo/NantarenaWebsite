<?php

namespace Nantarena\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nantarena\EventBundle\Validator\Constraints\DatesConstraint;
use Nantarena\EventBundle\Validator\Constraints\EntryTypesConstraint;
use Nantarena\EventBundle\Validator\Constraints\TournamentsConstraint;
use Nantarena\SiteBundle\Validator\Constraints\ResourceConstraint;

/**
 * Event
 *
 * @ORM\Table(name="event_event")
 * @ORM\Entity(repositoryClass="Nantarena\EventBundle\Repository\EventRepository")
 * @UniqueEntity("name")
 * @DatesConstraint(
 *      eventDates="event.dates.eventDates",
 *      registrationDates="event.dates.registrationDates",
 *      eventRegistrationDates="event.dates.eventRegistrationDates"
 * )
 * @TournamentsConstraint(
 *      message="event.tournaments.outDate"
 * )
 */
class Event
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
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", unique=true)
     */
    protected $slug;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime")
     * @Assert\DateTime()
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime")
     * @Assert\DateTime()
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_registration_date", type="datetime")
     * @Assert\DateTime()
     */
    private $startRegistrationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_registration_date", type="datetime")
     * @Assert\DateTime()
     */
    private $endRegistrationDate;

    /**
     * @var int
     *
     * @ORM\Column(name="capacity", type="smallint")
     * @Assert\GreaterThan(value=0)
     */
    private $capacity;

    /**
     * @ORM\OneToOne(
     *      targetEntity="Nantarena\SiteBundle\Entity\Image",
     *      cascade={"persist", "remove"},
     *      orphanRemoval=true)
     */
    private $cover;

    /**
     * @ORM\OneToOne(
     *      targetEntity="Nantarena\SiteBundle\Entity\Resource",
     *      cascade={"persist", "remove"},
     *      orphanRemoval=true
     * )
     * @ResourceConstraint(
     *     maxSize = "1024k",
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage = "event.rules.type",
     *     maxSizeMessage = "event.rules.size",
     *     uploadFormSizeErrorMessage = "event.rules.size",
     *     uploadErrorMessage = "event.rules.upload"
     * )
     */
    private $rules;

    /**
     * @ORM\OneToOne(
     *      targetEntity="Nantarena\SiteBundle\Entity\Resource",
     *      cascade={"persist", "remove"},
     *      orphanRemoval=true
     * )
     * @ResourceConstraint(
     *     maxSize = "1024k",
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage = "event.autorization.type",
     *     maxSizeMessage = "event.autorization.size",
     *     uploadFormSizeErrorMessage = "event.autorization.size",
     *     uploadErrorMessage = "event.autorization.upload"
     * )
     */
    private $autorization;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Nantarena\EventBundle\Entity\Tournament",
     *      mappedBy="event",
     *      cascade={"persist", "remove"})
     */
    private $tournaments;

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
     * @return EntryType
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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Event
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
     * @return Event
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
     * Set startRegistrationDate
     *
     * @param \DateTime $startRegistrationDate
     * @return Event
     */
    public function setStartRegistrationDate($startRegistrationDate)
    {
        $this->startRegistrationDate = $startRegistrationDate;
    
        return $this;
    }

    /**
     * Get startRegistrationDate
     *
     * @return \DateTime 
     */
    public function getStartRegistrationDate()
    {
        return $this->startRegistrationDate;
    }

    /**
     * Set endRegistrationDate
     *
     * @param \DateTime $endRegistrationDate
     * @return Event
     */
    public function setEndRegistrationDate($endRegistrationDate)
    {
        $this->endRegistrationDate = $endRegistrationDate;
    
        return $this;
    }

    /**
     * Get endRegistrationDate
     *
     * @return \DateTime 
     */
    public function getEndRegistrationDate()
    {
        return $this->endRegistrationDate;
    }

    /**
     * Set capacity
     *
     * @param integer $capacity
     * @return Event
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;
    
        return $this;
    }

    /**
     * Get capacity
     *
     * @return integer 
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tournaments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add tournament
     *
     * @param \Nantarena\EventBundle\Entity\Tournament $tournament
     * @return Event
     */
    public function addTournament(\Nantarena\EventBundle\Entity\Tournament $tournament)
    {
        $tournament->setEvent($this);
        $this->tournaments[] = $tournament;
    
        return $this;
    }

    /**
     * Remove tournament
     *
     * @param \Nantarena\EventBundle\Entity\Tournament $tournament
     */
    public function removeTournament(\Nantarena\EventBundle\Entity\Tournament $tournament)
    {
        $this->tournaments->removeElement($tournament);
    }

    /**
     * Get tournaments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTournaments()
    {
        return $this->tournaments;
    }

    /**
     * Set cover
     *
     * @param \Nantarena\SiteBundle\Entity\Image $cover
     * @return Event
     */
    public function setCover(\Nantarena\SiteBundle\Entity\Image $cover = null)
    {
        $this->cover = $cover;
    
        return $this;
    }

    /**
     * Get cover
     *
     * @return \Nantarena\SiteBundle\Entity\Image 
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Event
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set rules
     *
     * @param \Nantarena\SiteBundle\Entity\Resource $rules
     * @return Event
     */
    public function setRules(\Nantarena\SiteBundle\Entity\Resource $rules = null)
    {
        $this->rules = $rules;
    
        return $this;
    }

    /**
     * Get rules
     *
     * @return \Nantarena\SiteBundle\Entity\Resource 
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Set autorization
     *
     * @param \Nantarena\SiteBundle\Entity\Resource $autorization
     * @return Event
     */
    public function setAutorization(\Nantarena\SiteBundle\Entity\Resource $autorization = null)
    {
        $this->autorization = $autorization;
    
        return $this;
    }

    /**
     * Get autorization
     *
     * @return \Nantarena\SiteBundle\Entity\Resource 
     */
    public function getAutorization()
    {
        return $this->autorization;
    }

    /**
     * Get if event is full or not
     *
     * @return bool
     */
    public function isFull()
    {
        // TODO: Implement
        return false;
    }
}
