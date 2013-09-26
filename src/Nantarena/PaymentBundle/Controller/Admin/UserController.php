<?php

namespace Nantarena\PaymentBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Manage routing
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
// Request for form
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

// Entity
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Entity\Entry;

use Nantarena\PaymentBundle\Entity\Payment;
use Nantarena\PaymentBundle\Entity\CashPayment;

use Nantarena\UserBundle\Entity\User;


/**
 * Class UserController
 *
 * @package Nantarena\PaymentBundle\Controller\Admin
 *
 * @Route("/admin/payment/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/list/{slug}", name="nantarena_admin_payment_user_list", defaults={"slug" = null})
     * @Template()
     */
    public function listAction(Request $request, Event $event = null)
    {
        if (null === $event) {
            if (null === ($event = $this->getDoctrine()->getRepository('NantarenaEventBundle:Event')->findNext()))
                return array();
        }

        $form = $this->createEventChoiceForm($event)->handleRequest($request);

        if ($form->isValid()) {
            $e = $form->get('event')->getData();
            return $this->redirect($this->generateUrl('nantarena_admin_payment_user_list', array(
                'slug' => $e->getSlug()
            )));
        }

        $repository = $this->getDoctrine()
            ->getRepository('NantarenaUserBundle:User');
        $luser = $repository->findRegisteredEvent($event);

        return array(
            'luser' => $luser,
            'event' => $event,
            'form' => $form->createView()
        );
    }

    private function createEventChoiceForm(Event $event)
    {
        return $this->createFormBuilder(array('event' => $event))
            ->add('event', 'entity', array(
                'class' => 'NantarenaEventBundle:Event',
                'property' => 'name',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.startDate', 'DESC');
                }
            ))
            ->setMethod('POST')
            ->getForm();
    }
}