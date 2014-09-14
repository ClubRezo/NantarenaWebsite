<?php

namespace Nantarena\UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Nantarena\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validator;

class ProfileListener implements EventSubscriberInterface
{
    private $router;
    private $session;
    private $validator;
    private $translator;

    public function __construct(Router $router, Session $session, Validator $validator, Translator $translator)
    {
        $this->router = $router;
        $this->session = $session;
        $this->validator = $validator;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::PROFILE_EDIT_SUCCESS => 'redirect',
        );
    }

    public function redirect(FormEvent $event)
    {
        $redirect = $this->session->getFlashBag()->get('redirect');
        $flashbag = $this->session->getFlashBag();

        // Check if user profile is completed
        $errors = $this->validator->validate($event->getForm()->getData(), array('identity'));
        if($redirect != null AND count($errors) == 0) {
            $url = $redirect[0];
        }else{
            if($redirect != null) {
                $flashbag->add('redirect', $redirect[0]);
                $flashbag->add('error', $this->translator->trans('event.participate.flash.profile'));
            }
            $url = $this->router->generate('fos_user_profile_edit');
        }
        $event->setResponse(new RedirectResponse($url));
    }
}
