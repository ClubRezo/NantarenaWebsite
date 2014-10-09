<?php

namespace Nantarena\ForumBundle\Twig;

use Nantarena\ForumBundle\Entity\Forum;

class ForumExtension extends \Twig_Extension
{
    /**
     * @var \Nantarena\ForumBundle\Manager\ForumManager
     */
    protected $forumManager;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('forum_path', array($this, 'getForumPath')),
            new \Twig_SimpleFunction('forum_edit_path', array($this, 'getEditPath')),
            new \Twig_SimpleFunction('forum_create_path', array($this, 'getCreatePath')),
            new \Twig_SimpleFunction('forum_delete_path', array($this, 'getDeletePath')),
        );
    }

    public function getForumPath(Forum $forum)
    {
        return $this->forumManager->getForumPath($forum);
    }

    public function getDeletePath(Forum $forum)
    {
        return $this->forumManager->getDeletePath($forum);
    }

    public function getEditPath(Forum $forum)
    {
        return $this->forumManager->getEditPath($forum);
    }

    public function getCreatePath()
    {
        return $this->forumManager->getCreatePath();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'forum_forum_extension';
    }

    /**
     * @param \Nantarena\ForumBundle\Manager\ForumManager $forumManager
     */
    public function setForumManager($forumManager)
    {
        $this->forumManager = $forumManager;
    }
}
