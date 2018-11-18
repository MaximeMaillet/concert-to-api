<?php

namespace App\Form;

use App\Entity\User;
use App\Model\ArtistModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ArtistModelType extends AbstractType
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
        $builder
            ->add('name')
        ;

        if ($this->authorizationChecker->isGranted(User::ROLE_SCRAPPER)) {
            $builder->add('exact', null, [
                'empty_data' => true,
            ]);
        }

        if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            $builder->add('validated', null, [
                'empty_data' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ArtistModel::class,
            'allow_extra_fields' => true,
        ));
    }
}