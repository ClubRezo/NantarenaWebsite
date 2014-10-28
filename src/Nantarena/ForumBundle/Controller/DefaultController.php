<?php

namespace Nantarena\ForumBundle\Controller;

use Doctrine\ORM\NoResultException;
use Nantarena\SiteBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/forum")
 */
class DefaultController extends BaseController
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $categories = $this->getDoctrine()->getRepository('NantarenaForumBundle:Category')->findAllWithForums();

        $this->get('nantarena_site.breadcrumb')->push(
            $this->get('translator')->trans('forum.index.title'),
            $this->generateUrl('nantarena_forum_default_index')
        );

        return array(
            'categories' => $categories,
        );
    }

    /**
     * @Route("/unreads/{page}")
     * @Template()
     */
    public function unreadsAction($page = 1)
    {
        $this->get('nantarena_site.breadcrumb')
            ->push(
                $this->get('translator')->trans('forum.index.title'),
                $this->generateUrl('nantarena_forum_default_index')
            )
            ->push(
                $this->get('translator')->trans('forum.unreads.title'),
                $this->generateUrl('nantarena_forum_default_unreads')
            );

        $unreads = $this->getDoctrine()->getRepository('NantarenaForumBundle:ReadStatus')->findOneByUser($this->getUser());
        $pagination = $this->get('knp_paginator')->paginate(
            $unreads->getThreads(), $page, 20
        );

        return array(
            'pagination' => $pagination,
        );
    }

    /**
     * @Route("/read_all")
     */
    public function readAllAction()
    {
        if ($this->getSecurityContext()->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            try {
                $unreads = $this->getDoctrine()->getRepository('NantarenaForumBundle:ReadStatus')->findOneByUser($this->getUser());
                $unreads->clearThreads();
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', $this->get('translator')->trans('forum.read_all.success'));
            } catch (NoResultException $exception) {
                $this->addFlash('error', $this->get('translator')->trans('forum.read_all.error'));
            }
        } else {
            $this->addFlash('error', $this->get('translator')->trans('forum.read_all.error'));
        }

        return $this->redirect($this->generateUrl('nantarena_forum_default_index'));
    }
}
