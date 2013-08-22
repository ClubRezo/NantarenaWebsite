<?php

namespace Nantarena\PaymentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('submit', 'submit')
        ;
    }

    public function getName()
    {
        return 'nantarena_paymentbundle_payment';
    }
}