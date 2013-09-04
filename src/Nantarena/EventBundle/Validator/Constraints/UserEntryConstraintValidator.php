<?php

namespace Nantarena\EventBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class UserEntryConstraintValidator extends ConstraintValidator
{
    private $em;
    private $translator;

    public function __construct(EntityManager $em, Translator $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function validate($value, Constraint $constraint)
    {
        $registeredUsers = $this->em->getRepository('NantarenaUserBundle:User')->findRegisteredEvent($constraint->getEvent());

        if (in_array($value, $registeredUsers)) {
            $this->context->addViolation($this->translator->trans($constraint->alreadyRegistered, array(), 'validators'));
        }
    }
}
