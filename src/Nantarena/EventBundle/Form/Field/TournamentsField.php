<?php

namespace Nantarena\EventBundle\Form\Field;

use Doctrine\ORM\EntityManager;
use Nantarena\EventBundle\Form\Transformer\TournamentsTransformer;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\Translator;
use Doctrine\Common\Util\Debug;

class TournamentsField extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Component\Translation\Translator
     */
    private $translator;

    public function __construct(EntityManager $em, Translator $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tournaments = $this->em->getRepository('NantarenaEventBundle:Tournament')->findBy(array(
            'event' => $options['event']
        ));

        $exclusiveTournaments = array('' => $this->translator->trans('event.form.tournament.field.none')); #FIXME
        $otherTournaments = array();

        foreach ($tournaments as $tournament) {
            $name = $this->translator->trans('event.form.tournament.field.name', array(
                '%name%' => $tournament->getName(),
                '%nb%' => $tournament->getMaxTeams()
            ));

            if ($tournament->isExclusive())
                $exclusiveTournaments[$tournament->getId()] = $name;
            else
                $otherTournaments[$tournament->getId()] = $name;
        }

        $transformer = new TournamentsTransformer($this->em);

        $builder
            ->add('primary', 'choice', array(
                'choices' => $exclusiveTournaments,
                'expanded' => true,
                'label' => false
            ))
            ->add('secondary', 'choice', array(
                'choices' => $otherTournaments,
                'expanded' => true,
                'multiple' => true,
                'label' => false,
                'required' => false
            ))
            ->addViewTransformer($transformer)
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array(
                'event'
            ))
        ;
    }

    public function getParent()
    {
        return 'form';
    }

    public function getName()
    {
        return 'tournaments';
    }
}
