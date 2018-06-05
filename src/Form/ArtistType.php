<?php

namespace App\Form;

use App\Entity\Artist;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ArtistType extends AbstractType
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * ArtistType constructor.
     * @param $authorizationChecker
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerInterface $entityManager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('logo', TextType::class)
            ->add('events', EntityType::class, [
                'class' => Event::class,
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'choice_label' => 'id',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.name', 'ASC');
                }
            ])
        ;

        if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            $builder->add('validated', CheckboxType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Artist::class,
            'allow_extra_fields' => true,
        ));
    }
}