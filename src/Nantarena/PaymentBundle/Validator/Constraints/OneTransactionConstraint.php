<?php

namespace Nantarena\PaymentBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OneTransactionConstraint extends Constraint
{
    public $message = 'Il y a un conflit de paiement avec %person%.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'one_transaction_constraint';
    }
}