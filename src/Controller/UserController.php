<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Traits\SerializerTrait;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class UserController
 * @package App\Controller
 */
class UserController extends FOSRestController
{
    use SerializerTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * UserController constructor.
     * @param EntityManagerInterface $entityManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @return array|bool|float|int|object|string
     */
    public function getUsersAction(Request $request)
    {
        $users = $this->entityManager
            ->getRepository(User::class)
            ->findBy(['isActive' => true])
        ;

        return $this->normalize($users);
    }

    /**
     * @param Request $request
     * @IsGranted("ROLE_USER")
     * @param User $user
     * @return array|bool|float|int|object|string
     */
    public function getUserAction(Request $request, User $user)
    {
        return $this->normalize($user);
    }

    /**
     * @param Request $request
     * @IsGranted("ROLE_USER")
     * @param User $user
     * @return array|object|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function patchUserAction(Request $request, User $user)
    {
        if (!$this->authorizationChecker->isGranted(User::ROLE_ADMIN) && $user->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserType::class, $user, ['method' => 'PATCH']);
        try {
            $form->submit($request->request->all(), false);
        } catch(AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->normalize($user);
        }

        return $this->renderFormErrors($form);
    }
}