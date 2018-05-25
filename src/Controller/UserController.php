<?php

namespace App\Controller;

use App\Entity\User;
use App\Traits\SerializerTrait;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

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
     * UserController constructor.
     * @param $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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

        return $this->serialize($users);
    }

    /**
     * @param Request $request
     * @IsGranted("ROLE_USER")
     * @param User $user
     * @return array|bool|float|int|object|string
     */
    public function getUserAction(Request $request, User $user)
    {
        return $this->serialize($user);
    }
}