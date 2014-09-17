<?php

namespace Nantarena\EventBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Nantarena\EventBundle\Validator\Constraints\UserEntryConstraint;
use Nantarena\SiteBundle\Form\Field\TypeaheadField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userFieldOptions = array(
            'class' => 'NantarenaUserBundle:User',
            'property' => 'username',
            'invalid_message' => 'event.user.invalid',
            'disabled' => $options['edit']
        );

        if (!$options['edit']) {
            $userFieldOptions['constraints'] = array(
                new UserEntryConstraint($options['event'])
            );
        }

        $builder
            ->add('event', 'text', array(
                'mapped' => false,
                'disabled' => true,
                'data' => $options['event']->getName()
            ))
            ->add('tournament', 'entity', array(
                'class' => 'NantarenaEventBundle:Tournament',
                'property' => 'name',
                'query_builder' => function(EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('t')
                        ->where('t.event = :event')
                        ->setParameter('event', $options['event']);
                }
            ))
            ->add('team', 'entity', array(
                'class' => 'NantarenaEventBundle:Team',
                'property' => 'name',
                'required' => false,
                'query_builder' => function(EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('t')
                        ->join('t.tournament', 'to')
                        ->where('to.event = :event')
                        ->setParameter('event', $options['event']);
                }
            ))
            ->add('user', new TypeaheadField(), $userFieldOptions)
            ->add('submit', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'Nantarena\EventBundle\Entity\Entry',
                'edit' => false
            ))
            ->setRequired(array(
                'event'
            ))
        ;
    }

    public function getName()
    {
        return 'nantarena_eventbundle_entrytype';
    }
}
