<?php

namespace App\Form;

use App\Model\LocationModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationModelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('address')
            ->add('postalCode')
            ->add('city')
            ->add('country')
            ->add('longitude')
            ->add('latitude')
            ->add('longitude')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => LocationModel::class,
            'allow_extra_fields' => true,
        ));
    }
}