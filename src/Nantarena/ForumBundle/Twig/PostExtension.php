<?php

namespace Nantarena\ForumBundle\Twig;

use Nantarena\ForumBundle\Entity\Post;

class PostExtension extends \Twig_Extension
{
    /**
     * @var \Nantarena\ForumBundle\Manager\PostManager
     */
    protected $postManager;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('post_edit_path', array($this, 'getEditPath')),
            new \Twig_SimpleFunction('post_delete_path', array($this, 'getDeletePath')),
        );
    }

    public function getEditPath(Post $post)
    {
        return $this->postManager->getEditPath($post);
    }

    public function getDeletePath(Post $post)
    {
        return $this->postManager->getDeletePath($post);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'forum_post_extension';
    }

    /**
     * @param \Nantarena\ForumBundle\Manager\PostManager $postManager
     */
    public function setPostManager($postManager)
    {
        $this->postManager = $postManager;
    }
}
