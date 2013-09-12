<?php

namespace Nantarena\PaymentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('partners', 'choice', array(
                'choices'   => $options['userList'],
                'expanded' => true,
                'multiple'  => true,
            ))
            ->add('submit', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array(
                'userList'
            ))
        ;
    }

    public function getName()
    {
        return 'nantarena_paymentbundle_payment';
    }
}