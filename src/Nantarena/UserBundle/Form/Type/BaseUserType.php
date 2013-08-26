<?php

namespace Nantarena\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BaseUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text')
            ->add('email', 'email')
            ->add('firstname', 'text', array('required' => false))
            ->add('lastname', 'text', array('required' => false))
            ->add('birthdate', 'birthday', array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'required' => false
            ))
            ->add('email', 'email')
            ->add('groups', 'entity', array(
                'class' => 'NantarenaUserBundle:Group',
                'property' => 'name',
                'multiple' => true
            ))
            ->add('enabled', 'checkbox', array('required' => false))
            ->add('locked', 'checkbox', array('required' => false))
            ->add('submit', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nantarena\UserBundle\Entity\User',
            'validation_groups' => array('Profile', 'Default'),
        ));
    }

    public function getName()
    {
        return 'nantarena_userbundle_usertype';
    }
}
