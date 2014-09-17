<?php

namespace Nantarena\EventBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Nantarena\SiteBundle\Form\Field\TypeaheadField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userFieldOptions = array(
            'class' => 'NantarenaUserBundle:User',
            'property' => 'username',
            'invalid_message' => 'event.user.notyet',
            'query_builder' => function(EntityRepository $er) use ($options) {
                return $er->createQueryBuilder('u')
                    ->leftJoin('u.entries', 'ee')
                    ->leftJoin('ee.tournament', 'et')
                    ->where('et.event = :event')
                    ->setParameter('event', $options['event']);
            }
        );

        $builder
            ->add('event', 'text', array(
                'mapped' => false,
                'disabled' => true,
                'data' => $options['event']->getName()
            ))
            ->add('name', 'text')
            ->add('tag', 'text')
            ->add('logo', 'url', array(
                'required' => false
            ))
            ->add('website', 'url', array(
                'required' => false
            ))
            ->add('desc', 'textarea', array(
                'required' => false
            ))
            ->add('tournaments', 'tournaments', array(
                'event' => $options['event'],
                'label' => false
            ))
            ->add('members', 'collection', array(
                'type' => new TypeaheadField(),
                'options' => $userFieldOptions,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('creator', new TypeaheadField(), $userFieldOptions)
            ->add('submit', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'Nantarena\EventBundle\Entity\Team',
            ))
            ->setRequired(array(
                'event'
            ))
        ;
    }

    public function getName()
    {
        return 'nantarena_eventbundle_teamtype';
    }
}
