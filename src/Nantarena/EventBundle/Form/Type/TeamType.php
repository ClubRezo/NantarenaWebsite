<?php

namespace Nantarena\EventBundle\Form\Type;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Nantarena\SiteBundle\Form\Field\TypeaheadField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('event', 'text', array(
                'mapped' => false,
                'disabled' => true,
                'data' => $options['event']->getName()
            ))
            ->add('name', 'text')
            ->add('tag', 'text', array(
                'required' => false
            ))
            ->add('logo', 'url', array(
                'required' => false
            ))
            ->add('website', 'url', array(
                'required' => false
            ))
            ->add('desc', 'textarea', array(
                'required' => false
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
            ->add('members', 'collection', array(
                    'type' => new TeamMemberType(),
                    'options' => array(
                        'event' => $options['event'],
                        'em' => $options['em']
                    ),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                )
            )
            ->add('creator', new TeamMemberType(), array(
                'event' => $options['event'],
                'em' => $options['em']
            ))
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
                'event',
                'em'
            ))
            ->setAllowedTypes(array(
                'em' => 'Doctrine\Common\Persistence\ObjectManager',
            ));
        ;
    }

    public function getName()
    {
        return 'nantarena_eventbundle_teamtype';
    }
}
