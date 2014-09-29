<?php

namespace Nantarena\UserBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Nantarena\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function linkDefaultGroup(FilterUserResponseEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();

        $groups = $this->em->getRepository('NantarenaUserBundle:Group')->findBy(array(
            'default' => true
        ));

        foreach($groups as $group) {
            $user->addGroup($group);
            $this->em->persist($user);
        }

        $this->em->flush();
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_COMPLETED => 'linkDefaultGroup'
        );
    }
}
