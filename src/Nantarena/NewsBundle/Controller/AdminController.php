<?php

namespace Nantarena\NewsBundle\Controller;

use Nantarena\AdminBundle\Controller\DashboardInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller implements DashboardInterface
{
    public function dashboardAction()
    {
        $translator = $this->get('translator');

        return array(
            'module_title' => $translator->trans('news.admin.dashboard.title'),
            'module_links' => array(
                $translator->trans('news.admin.dashboard.news_management') => array(
                    'url' => $this->generateUrl('nantarena_news_admin_index'),
                    'role' => 'ROLE_NEWS_ADMIN_NEWS'
                ),

                $translator->trans('news.admin.dashboard.categories_management') => array(
                    'url' => $this->generateUrl('nantarena_news_admin_categories_index'),
                    'role' => 'ROLE_NEWS_ADMIN_NEWS'
                ),
            )
        );
    }
}
