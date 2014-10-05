<?php

namespace Nantarena\EventBundle\Controller;

use Nantarena\AdminBundle\Controller\DashboardInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AdminController extends Controller implements DashboardInterface
{
    public function dashboardAction()
    {
        $translator = $this->get('translator');

        return array(
            'module_title' => $translator->trans('event.admin.dashboard.title'),
            'module_links' => array(
                $translator->trans('event.admin.dashboard.games_management') => array(
                    'url' => $this->generateUrl('nantarena_event_admin_games'),
                    'role' => 'ROLE_EVENT_ADMIN_EVENTS'
                ),

                $translator->trans('event.admin.dashboard.events_management') => array(
                    'url' => $this->generateUrl('nantarena_event_admin_events'),
                    'role' => 'ROLE_EVENT_ADMIN_EVENTS'
                ),

                $translator->trans('event.admin.dashboard.entries_management') => array(
                    'url' => $this->generateUrl('nantarena_event_admin_entries'),
                    'role' => 'ROLE_EVENT_ADMIN_ENTRIES'
                ),

                $translator->trans('event.admin.dashboard.teams_management') => array(
                    'url' => $this->generateUrl('nantarena_event_admin_teams'),
                    'role' => 'ROLE_EVENT_ADMIN_ENTRIES'
                ),
            )
        );
    }
}
