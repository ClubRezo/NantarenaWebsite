<?php

namespace Nantarena\NewsBundle\Twig;

use Nantarena\NewsBundle\Entity\News;

class NewsExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * @var \Nantarena\NewsBundle\Manager\NewsManager
     */
    protected $newsManager;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('news_state', array($this, 'getNewsState')),
            new \Twig_SimpleFunction('news_path', array($this, 'getNewsPath')),
            new \Twig_SimpleFunction('news_edit_path', array($this, 'getEditPath')),
            new \Twig_SimpleFunction('news_delete_path', array($this, 'getDeletePath')),
        );
    }

    public function getNewsPath(News $news)
    {
        return $this->newsManager->getNewsPath($news);
    }

    /**
     * @param \Nantarena\NewsBundle\Manager\NewsManager $newsManager
     */
    public function setNewsManager($newsManager)
    {
        $this->newsManager = $newsManager;
    }

    /**
     * @param \Symfony\Component\Translation\Translator $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function getEditPath(News $news)
    {
        return $this->newsManager->getEditPath($news);
    }

    public function getDeletePath(News $news)
    {
        return $this->newsManager->getDeletePath($news);
    }

    public function getNewsState(News $news)
    {
        if (true == $news->getState()) {
            return $this->translator->trans('news.state.published');
        } else {
            return $this->translator->trans('news.state.unpublished');
        }
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'news_extension';
    }
}
