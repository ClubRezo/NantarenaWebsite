<?php

namespace Nantarena\NewsBundle\Twig;

use Nantarena\NewsBundle\Entity\Category;
use Nantarena\NewsBundle\Entity\News;

class CategoryExtension extends \Twig_Extension
{
    /**
     * @var \Nantarena\NewsBundle\Manager\CategoryManager
     */
    protected $categoryManager;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('category_path', array($this, 'getCategoryPath')),
            new \Twig_SimpleFunction('category_edit_path', array($this, 'getEditPath')),
            new \Twig_SimpleFunction('category_delete_path', array($this, 'getDeletePath')),
        );
    }

    public function getCategoryPath(Category $category)
    {
        return $this->categoryManager->getCategoryPath($category);
    }

    public function getEditPath(Category $category)
    {
        return $this->categoryManager->getEditPath($category);
    }

    public function getDeletePath(Category $category)
    {
        return $this->categoryManager->getDeletePath($category);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'category_extension';
    }

    /**
     * @param \Nantarena\NewsBundle\Manager\CategoryManager $categoryManager
     */
    public function setCategoryManager($categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }
}
