<?php

namespace Nantarena\EventBundle\Services;

use Nantarena\EventBundle\Entity\Entry;
use Nantarena\EventBundle\Entity\Team;
use Nantarena\PaymentBundle\Payment\PaymentService;

class TeamService
{
    /** @var PaymentService $payment */
    protected $payment;

    public function __construct(PaymentService $payment)
    {
        $this->payment = $payment;
    }

    /**
     * @param Team $team
     * @return bool
     */
    public function isValid(Team $team) {
        $members = $team->getMembers();
        $capacity = $team->getTournament()->getGame()->getTeamCapacity();
        $threesold = ceil($capacity / 2);
        $paid = 0;

        if (count($members) < $capacity) {
            return false;
        }

        /** @var Entry $member */
        foreach($members as $member) {
            $transaction = $this->payment->getValidTransaction($member);

            if (null !== $transaction) {
                $paid++;
            }
        }

        return ($paid >= $threesold);
    }
}
