<?php

namespace App\Form;

use App\Entity\User;
use App\Model\EventModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventModelType extends AbstractType
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * ArtistModelType constructor.
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->authorizationChecker->isGranted(User::ROLE_SCRAPPER)) {
            $builder->add('exact', null, [
                'empty_data' => true,
            ]);
            $builder->add('hash');
        } else {
            $builder
                ->add('name')
                ->add('startDate', DateTimeType::class, [
                    'widget' => 'single_text'
                ])
                ->add('endDate')
            ;

            if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
                // @todo validated
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EventModel::class,
            'allow_extra_fields' => true,
        ));
    }
}