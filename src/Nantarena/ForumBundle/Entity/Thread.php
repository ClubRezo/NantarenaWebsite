<?php

namespace Nantarena\ForumBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Nantarena\UserBundle\Entity\User;
use Nantarena\ForumBundle\Validator\Constraints as ForumAssert;

/**
 * Thread
 *
 * @ORM\Table(name="forum_thread")
 * @ORM\Entity(repositoryClass="Nantarena\ForumBundle\Repository\ThreadRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Thread
{
    const POSTS_PER_PAGE = 15;

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
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Nantarena\ForumBundle\Entity\Post", mappedBy="thread", cascade={"remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $posts;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Nantarena\UserBundle\Entity\User")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updateDate", type="datetime")
     */
    private $updateDate;

    /**
     * @var Forum
     *
     * @ORM\ManyToOne(targetEntity="Nantarena\ForumBundle\Entity\Forum", inversedBy="threads")
     */
    private $forum;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $locked;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @ForumAssert\Sticky
     */
    private $sticky;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    public function __construct()
    {
        $this->updateDate = new \DateTime();
        $this->posts = new ArrayCollection();
        $this->locked = false;
        $this->sticky = false;
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
     * @return Thread
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
     * Set slug
     *
     * @param string $slug
     * @return Thread
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
     * Set posts
     *
     * @param string $posts
     * @return Thread
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
    
        return $this;
    }

    /**
     * Get posts
     *
     * @return ArrayCollection
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Set creator
     *
     * @param string $creator
     * @return Thread
     */
    public function setUser($creator)
    {
        $this->user = $creator;
    
        return $this;
    }

    /**
     * Get creator
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set updateDate
     *
     * @param \DateTime $updateDate
     * @return Thread
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    
        return $this;
    }

    /**
     * Get updateDate
     *
     * @return \DateTime 
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * Set forum
     *
     * @param Forum $forum
     * @return Thread
     */
    public function setForum($forum)
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * Get forum
     *
     * @return Forum
     */
    public function getForum()
    {
        return $this->forum;
    }

    public function getLastPage()
    {
        return ceil($this->posts->count() / self::POSTS_PER_PAGE);
    }

    public function getPageForPost(Post $post)
    {
        $index = $this->posts->indexOf($post) + 1;
        $page = 1;

        if (false !== $index) {
            $page = ceil($index / self::POSTS_PER_PAGE);
        }

        return $page;
    }

    /**
     * @param Post $post
     * @return Thread
     */
    public function addPost(Post $post)
    {
        $this->posts->add($post);

        return $this;
    }

    public function updateActivity()
    {
        $this->updateDate = new \DateTime();
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function isOpen()
    {
        return !$this->locked;
    }

    public function close()
    {
        $this->locked = true;
    }

    public function open()
    {
        $this->locked = false;
    }

    /**
     * @param \DateTime $deletedAt
     * @return Post
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    public function isSticky()
    {
        return $this->sticky;
    }

    public function setSticky($sticky)
    {
        $this->sticky = $sticky;

        return $this;
    }
}
