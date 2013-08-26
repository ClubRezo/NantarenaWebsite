<?php

namespace Nantarena\PaymentBundle\Payment;

use Doctrine\ORM\EntityManager;

class PaymentService
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    function getValidTransaction($entry)
    {
        $repository = $this->em->getRepository('NantarenaPaymentBundle:Transaction');
        $transaction = $repository->findOneBy(array('user' => $entry->getUser(), 
            'event' => $entry->getEntryType()->getEvent(), 'refund' => null));

        if ($transaction && $transaction->getPayment()->getValid()) {
            return $transaction;
        } else {
            return null;
        }
    }

    function isPaid($entry)
    {
        $result = $this->getValidTransaction($entry);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    function isTransaction($entry)
    {
        return true;
    }
}
