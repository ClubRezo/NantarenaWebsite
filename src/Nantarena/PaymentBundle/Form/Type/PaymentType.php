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
        if (!empty($options['partnerList'])) {
            $builder
                ->add('partners', 'choice', array(
                    'choice_list' => $options['partnerList'],
                    'expanded' => true,
                    'multiple'  => true,
                ));
        }
        
        $builder
            ->add('submit', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array(
                'partnerList'
            ))
        ;
    }

    public function getName()
    {
        return 'nantarena_paymentbundle_payment';
    }
}