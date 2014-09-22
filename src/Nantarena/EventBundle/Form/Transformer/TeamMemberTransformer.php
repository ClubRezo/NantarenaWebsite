<?php

namespace Nantarena\EventBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManager;

class TeamMemberTransformer implements DataTransformerInterface
{
    /** @var  EntityManager */
    private $em;

    private $event;

    public function __construct($em, $event)
    {
        $this->em = $em;
        $this->event = $event;
    }

    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        $result = null;

        if ($value->getUser() !== null) {
            $result = $this->em
                ->getRepository("NantarenaEventBundle:Entry")
                ->findByEventAndUser($this->event, $value->getUser());
        }

        return $result;
    }

}
