<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Services\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    protected $userService;

    protected $passwordEncoder;

    /**
     * AppFixtures constructor.
     */
    public function __construct(UserService $userService, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->userService = $userService;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->addAdmin($manager);
        $this->addSuperAdmin($manager);
    }

    protected function addAdmin(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail(getenv('USER_ADMIN_EMAIL'));
        $user->setPlainPassword(getenv('USER_ADMIN_PASSWORD'));
        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        $user->setIsActive(true);
        $user->addRole(User::ROLE_ADMIN);

        $manager->persist($user);
        $manager->flush();
    }

    protected function addSuperAdmin(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail(getenv('USER_SUPER_ADMIN_EMAIL'));
        $user->setPlainPassword(getenv('USER_SUPER_ADMIN_PASSWORD'));
        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        $user->setIsActive(true);
        $user->addRole(User::ROLE_ADMIN);
        $user->addRole(User::ROLE_SUPER_ADMIN);

        $manager->persist($user);
        $manager->flush();
    }
}