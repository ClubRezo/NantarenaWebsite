<?php

namespace Nantarena\ForumBundle\Controller;

use Nantarena\ForumBundle\Entity\ReadStatus;
use Nantarena\ForumBundle\Entity\Thread;
use Nantarena\SiteBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class WidgetController extends BaseController
{
    /**
     * Widget pour renderer les sujets récents du forum
     *
     * Il met aussi à jour l'activité de la personne sur le site
     *
     * @Template()
     */
    public function recentAction($limit = 10)
    {
        $em = $this->getDoctrine()->getManager();
        $threads = $this->getDoctrine()->getRepository('NantarenaForumBundle:Thread')->findRecents($limit);
        /** @var ReadStatus $status */
        $status = null;

        if ($this->getSecurityContext()->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            $status = $this->getDoctrine()->getRepository('NantarenaForumBundle:ReadStatus')->findOneByUser($this->getUser());
            // récupération des derniers plus récents (non lus)
            $unreads = $this->getDoctrine()->getRepository('NantarenaForumBundle:Thread')->findUnreads($status->getUpdateDate());

            // filtre uniquement ceux auquel l'user à accès
            $unreads = array_filter($unreads, function(Thread $thread) {
                return $this->getSecurityContext()->isGranted('VIEW', $thread->getForum());
            });

            $status
                // ajoute les threads dans le Read Status
                ->addThreads($unreads)
                // finalement on update la date d'activité
                ->setUpdateDate(new \DateTime());

            $em->flush();
        }

        return array(
            'widget_threads' => $threads,
            'status' => $status,
        );
    }
}
