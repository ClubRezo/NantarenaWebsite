<?php

namespace Nantarena\ForumBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nantarena\UserBundle\Entity\User;

/**
 * ReadStatus
 *
 * @ORM\Table(name="forum_read_status")
 * @ORM\Entity(repositoryClass="Nantarena\ForumBundle\Repository\ReadStatusRepository")
 */
class ReadStatus
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
     * @var User
     *
     * @ORM\OneToOne(targetEntity="Nantarena\UserBundle\Entity\User")
     */
    protected $user;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Nantarena\ForumBundle\Entity\Thread")
     * @ORM\JoinTable(name="forum_read_status_thread")
     * @ORM\OrderBy({"updateDate" = "DESC"})
     */
    protected $threads;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetime")
     */
    protected $updateDate;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->updateDate = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ArrayCollection $threads
     * @return $this
     */
    public function setThreads(ArrayCollection $threads)
    {
        $this->threads = $threads;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getThreads()
    {
        return $this->threads;
    }

    /**
     * @param \DateTime $updateDate
     * @return $this
     */
    public function setUpdateDate(\DateTime $updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Nantarena\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function addThread(Thread $thread)
    {
        $this->threads->add($thread);

        return $this;
    }

    public function removeThread(Thread $thread)
    {
        $this->threads->removeElement($thread);
    }

    /**
     * Ajoute plusieurs threads à la liste des non lus
     *
     * @param array $threads
     * @return ReadStatus
     */
    public function addThreads(array $threads)
    {
        foreach ($threads as $thread) {
            if (false === $this->threads->indexOf($thread)) {
                $this->threads->add($thread);
            }
        }

        return $this;
    }

    /**
     * Supprime tous les topics non lus du ReadStatus
     */
    public function clearThreads()
    {
        $this->threads->clear();
    }
}
