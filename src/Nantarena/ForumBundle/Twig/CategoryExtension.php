<?php

namespace Nantarena\ForumBundle\Twig;

use Nantarena\ForumBundle\Entity\Category;

class CategoryExtension extends \Twig_Extension
{
    /**
     * @var \Nantarena\ForumBundle\Manager\CategoryManager
     */
    protected $categoryManager;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('forum_category_path', array($this, 'getCategoryPath')),
            new \Twig_SimpleFunction('forum_category_delete_path', array($this, 'getDeletePath')),
            new \Twig_SimpleFunction('forum_category_edit_path', array($this, 'getEditPath')),
            new \Twig_SimpleFunction('forum_category_create_path', array($this, 'getCreatePath')),
        );
    }

    public function getCategoryPath(Category $category)
    {
        return $this->categoryManager->getCategoryPath($category);
    }

    public function getDeletePath(Category $category)
    {
        return $this->categoryManager->getDeletePath($category);
    }

    public function getEditPath(Category $category)
    {
        return $this->categoryManager->getEditPath($category);
    }

    public function getCreatePath()
    {
        return $this->categoryManager->getCreatePath();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'forum_category_extension';
    }

    /**
     * @param \Nantarena\ForumBundle\Manager\CategoryManager $categoryManager
     */
    public function setCategoryManager($categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }
}
