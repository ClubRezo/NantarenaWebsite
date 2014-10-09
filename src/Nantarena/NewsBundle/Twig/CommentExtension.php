<?php

namespace Nantarena\NewsBundle\Twig;

use Nantarena\NewsBundle\Entity\Comment;
use Nantarena\NewsBundle\Entity\News;

class CommentExtension extends \Twig_Extension
{
    /**
     * @var \Nantarena\NewsBundle\Manager\CommentManager
     */
    protected $commentManager;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('comment_create_path', array($this, 'getCreateCommentPath')),
            new \Twig_SimpleFunction('comment_delete_path', array($this, 'getDeleteCommentPath')),
        );
    }

    public function getCreateCommentPath(News $news)
    {
        return $this->commentManager->getCreateCommentPath($news);
    }

    public function getDeleteCommentPath(Comment $comment)
    {
        return $this->commentManager->getDeleteCommentPath($comment);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'comment_extension';
    }

    /**
     * @param \Nantarena\NewsBundle\Manager\CommentManager $commentManager
     */
    public function setCommentManager($commentManager)
    {
        $this->commentManager = $commentManager;
    }
}
