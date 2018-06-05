<?php

namespace App\Form;


use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\DateFormatter\IntlDateFormatter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventType extends AbstractType
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * EventType constructor.
     * @param $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('startDate', DateTimeType::class, [
                'widget' => 'single_text'
            ])
            ->add('endDate', DateTimeType::class, [
                'widget' => 'single_text'
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'id',
                'query_builder' => function (EntityRepository $er) {
                    if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
                        return $er->createQueryBuilder('l')
                            ->orderBy('l.name', 'ASC');
                    } else {
                        return $er->createQueryBuilder('l')
                            ->where('l.validated = 1')
                            ->orderBy('l.name', 'ASC');
                    }
                }
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