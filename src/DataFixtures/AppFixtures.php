<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Services\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    protected $userService;

    /**
     * AppFixtures constructor.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('maxime.maillet93@gmail.com');
        $user->setPlainPassword('21022102');
        $user->setIsActive(true);
//        $this->userService->createUser($user);

        $manager->persist($user);
        $manager->flush();
    }
}