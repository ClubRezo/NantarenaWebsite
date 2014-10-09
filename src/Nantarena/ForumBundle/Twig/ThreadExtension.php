<?php

namespace Nantarena\ForumBundle\Twig;

use Nantarena\ForumBundle\Entity\Forum;
use Nantarena\ForumBundle\Entity\Thread;

class ThreadExtension extends \Twig_Extension
{
    /**
     * @var \Nantarena\ForumBundle\Manager\ThreadManager
     */
    protected $threadManager;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('thread_path', array($this, 'getThreadPath')),
            new \Twig_SimpleFunction('thread_reply_path', array($this, 'getThreadReplyPath')),
            new \Twig_SimpleFunction('thread_create_path', array($this, 'getThreadCreatePath')),
            new \Twig_SimpleFunction('thread_delete_path', array($this, 'getThreadDeletePath')),
            new \Twig_SimpleFunction('thread_lock_path', array($this, 'getThreadLockPath')),
            new \Twig_SimpleFunction('thread_move_path', array($this, 'getThreadMovePath')),
        );
    }

    public function getThreadPath(Thread $thread, $page = 1)
    {
        return $this->threadManager->getThreadPath($thread, $page);
    }

    public function getThreadReplyPath(Thread $thread)
    {
        return $this->threadManager->getReplyPath($thread);
    }

    public function getThreadCreatePath(Forum $forum)
    {
        return $this->threadManager->getCreatePath($forum);
    }

    public function getThreadLockPath(Thread $thread)
    {
        return $this->threadManager->getLockPath($thread);
    }

    public function getThreadDeletePath(Thread $thread)
    {
        return $this->threadManager->getDeletePath($thread);
    }

    public function getThreadMovePath(Thread $thread)
    {
        return $this->threadManager->getMovePath($thread);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'forum_thread_extension';
    }

    /**
     * @param \Nantarena\ForumBundle\Manager\ThreadManager $threadManager
     */
    public function setThreadManager($threadManager)
    {
        $this->threadManager = $threadManager;
    }
}
