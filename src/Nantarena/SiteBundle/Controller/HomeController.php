<?php

namespace Nantarena\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class HomeController extends Controller
{
    /**
     * @Route("/", name="nantarena_site_home")
     * @Template()
     */
    public function homeAction()
    {
        $latest = $this->getDoctrine()->getRepository('NantarenaNewsBundle:News')->findBy(array(), array(
            'id' => 'desc',
        ), 5);

        return array(
            'latest' => $latest,
        );
    }
}
