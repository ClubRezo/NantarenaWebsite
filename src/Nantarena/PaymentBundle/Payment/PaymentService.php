<?php

namespace Nantarena\PaymentBundle\Payment;

use Doctrine\ORM\EntityManager;
use Nantarena\EventBundle\Entity\Entry;

class PaymentService
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Entry $entry
     * @return \Nantarena\PaymentBundle\Entity\Transaction|null
     */
    function getValidTransaction($entry)
    {
        $repository = $this->em->getRepository('NantarenaPaymentBundle:Transaction');
        $transaction = $repository->findOneBy(array(
            'user' => $entry->getUser(),
            'event' => $entry->getTournament()->getEvent(),
            'refund' => null
        ));

        if ($transaction && $transaction->getPayment()->getValid()) {
            return $transaction;
        } else {
            return null;
        }
    }

    function isPaid($entry)
    {
        $result = $this->getValidTransaction($entry);
        return (null !== $result);
    }
}
