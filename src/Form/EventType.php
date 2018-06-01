<?php

namespace App\Form;


use App\Entity\Event;
use App\Entity\Location;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\DateFormatter\IntlDateFormatter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('startDate', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text'
            ])
            ->add('endDate', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text'
            ])
            ->add('location', LocationType::class, [
//                'class' => Location::class,
//                'choice_label' => 'id',
//                'query_builder' => function (EntityRepository $er) {
//                    return $er->createQueryBuilder('l')
//                        ->orderBy('l.name', 'ASC');
//                }
            ])
            ->add('artists', CollectionType::class, [
                'required' => false,
                'entry_type' => ArtistType::class
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Event::class,
            'allow_extra_fields' => true,
        ));
    }
}