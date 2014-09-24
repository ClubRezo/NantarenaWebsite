<?php

namespace Nantarena\EventBundle\Form\Type;

use Nantarena\EventBundle\Form\Transformer\TeamMemberTransformer;
use Nantarena\SiteBundle\Form\Field\TypeaheadField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class TeamMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', new TypeaheadField(), array(
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
            ))
            ->addViewTransformer(new TeamMemberTransformer($options['em'], $options['event']))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'Nantarena\EventBundle\Entity\Entry',
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
        return 'nantarena_eventbundle_teammembertype';
    }
}
