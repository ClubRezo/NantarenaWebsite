<?php

namespace Nantarena\StaticBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class StaticContent
 *
 * @package Nantarena\StaticBundle\Entity
 *
 * @ORM\Entity(repositoryClass="Nantarena\StaticBundle\Repository\StaticContentRepository")
 * @ORM\Table(name="static_content")
 * @UniqueEntity("title")
 * @ORM\HasLifecycleCallbacks
 */
class StaticContent
{
    const STATE_PUBLISHED = true;
    const STATE_UNPUBLISHED = false;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $state = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, unique=true)
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="update_date")
     * @Assert\DateTime()
     */
    protected $update;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=64, unique=true)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    protected $content;

    public function __construct()
    {
        $this->update = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate() {
        $this->update = new \DateTime();
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param \DateTime $update
     * @return $this
     */
    public function setUpdate(\DateTime $update)
    {
        $this->update = $update;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * @param boolean $state
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
