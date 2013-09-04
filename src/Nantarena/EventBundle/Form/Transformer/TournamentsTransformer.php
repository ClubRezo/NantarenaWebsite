<?php

namespace Nantarena\EventBundle\Form\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Util\Debug;

class TournamentsTransformer implements DataTransformerInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function transform($value)
    {
        $result = array(
            'primary' => '',
            'secondary' => array()
        );

        /** @var $tournament \Nantarena\EventBundle\Entity\Tournament */
        foreach ($value as $tournament) {
            if ($tournament->isExclusive()) {
                $result['primary'] = $tournament->getId();
            } else {
                $result['secondary'][] = $tournament->getId();
            }
        }

        return $result;
    }

    public function reverseTransform($value)
    {
        $tournaments = new ArrayCollection();

        if (!empty($value['primary'])) {
            $primary = $this->em->getRepository('NantarenaEventBundle:Tournament')->find($value['primary']);

            if (null !== $primary)
                $tournaments->add($primary);
        }

        foreach ($value['secondary'] as $tournament) {
            $secondary = $this->em->getRepository('NantarenaEventBundle:Tournament')->find($tournament);

            if (null !== $secondary)
                $tournaments->add($secondary);
        }

        return $tournaments;
    }

}
