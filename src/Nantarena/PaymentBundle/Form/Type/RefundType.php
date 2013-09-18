<?php

namespace Nantarena\PaymentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class RefundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', 'textarea')
            ->add('valid', 'checkbox', array(
                'required'  => false,
            ))
            ->add('submit', 'submit')
        ;
    }

    public function getName()
    {
        return 'nantarena_paymentbundle_refund';
    }
}