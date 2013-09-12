<?php

namespace Nantarena\PaymentBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;

class OneTransactionConstraintValidator extends ConstraintValidator
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
        $repository = $this->em->getRepository('NantarenaPaymentBundle:Transaction');
        $transaction = $repository->findOneByEventAndUser($value->getEvent(), $value->getUser());

        if ($transaction) {
            $this->context->addViolation($constraint->message, array('%person%' => $value->getUser()->getUsername()));
        }
    }
}