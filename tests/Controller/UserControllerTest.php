<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Tests\Utils\CustomWebTestCase;
use App\Traits\SerializerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserControllerTest
 * @package App\Tests\Controller
 */
class UserControllerTest extends CustomWebTestCase
{
    use SerializerTrait;

    /**
     * @var array
     */
    protected $credentials = [];

    /**
     * @var User
     */
    protected $user;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp()
    {
        $this->credentials['email'] = $this->getCorrectEmail('userControllerTest');
        $this->credentials['password'] = 'testuser';

        parent::setUp();

        $entityManager = self::get('doctrine.orm.entity_manager');
        $passwordEncoder = self::get('security.password_encoder');

        $this->user = (new User())
            ->setEmail($this->credentials['email'])
            ->setPlainPassword($this->credentials['password'])
            ->addRole(User::ROLE_USER)
            ->setIsActive(true)
        ;
        $password = $passwordEncoder->encodePassword($this->user, $this->user->getPlainPassword());
        $this->user->setPassword($password);
        $entityManager->persist($this->user);
        $entityManager->flush();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->user = null;
    }

    public function testGetUser()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            self::get('router')->generate('get_user', [
                'user' => $this->user->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => 'Bearer '.$this->login($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        /** @var User $user */
        $user = $this->deserialize($client->getResponse()->getContent(), User::class);

        $this->assertEquals($this->user->getEmail(), $user->getEmail());
        $this->assertContains(User::ROLE_USER, $user->getRoles());
    }

    public function testGetUsers()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            self::get('router')->generate('get_users', [
                'user' => $this->user->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => 'Bearer '.$this->login($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $users = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(is_array($users));
    }

    public function testGetUserAsNotConnected()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            self::get('router')->generate('get_user', [
                'user' => $this->user->getId(),
            ]),
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testGetUsersAsNotConnected()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            self::get('router')->generate('get_users', [
                'user' => $this->user->getId(),
            ]),
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        $users = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(is_array($users));
    }

    public function testPatchUserAsAdmin()
    {
        $client = static::createClient();
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        /** @var User $admin */
        $admin = $userRepository->findOneBy(['email' => getenv('USER_ADMIN_EMAIL')]);

        $client = static::createClient();
        $changeEmail = $this->getCorrectEmail('otheremail');
        $client->request(
            'PATCH',
            self::get('router')->generate('patch_user', [
                'user' => $this->user->getId(),
            ]),
            [
                'email' => $changeEmail,
                'isActive' => true,
            ],
            [],
            [
                'HTTP_Authorization' => 'Bearer '.$this->login($admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $user = $userRepository->findOneBy(['id' => $this->user->getId()]);
        $this->assertEquals($changeEmail, $user->getEmail());
        $this->assertTrue($user->isActive());
    }

    public function testPatchUserAsNotConnected()
    {
        $client = static::createClient();
        $changeEmail = $this->getCorrectEmail('otheremail');
        $client->request(
            'PATCH',
            self::get('router')->generate('patch_user', [
                'user' => $this->user->getId(),
            ]),
            [
                'email' => $changeEmail,
                'isActive' => true,
            ]
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testPatchUserAsOtherUser()
    {
        $client = static::createClient();
        $changeEmail = $this->getCorrectEmail('otheremail');

        $user = $client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.id != :id')
            ->setParameter('id', $this->user->getId())
            ->andWhere('u.roles NOT LIKE :roles')
            ->setParameter('roles', '%ROLE_ADMIN%')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;

        $client->request(
            'PATCH',
            self::get('router')->generate('patch_user', [
                'user' => $this->user->getId(),
            ]),
            [
                'email' => $changeEmail,
                'isActive' => true,
            ],
            [],
            [
                'HTTP_Authorization' => 'Bearer '.$this->login($user)
            ]
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testPatchUserAsMe()
    {
        $client = static::createClient();
        $userRepository = $client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);

        $changeEmail = $this->getCorrectEmail('otheremail');
        $client->request(
            'PATCH',
            self::get('router')->generate('patch_user', [
                'user' => $this->user->getId(),
            ]),
            [
                'email' => $changeEmail,
                'isActive' => true,
            ],
            [],
            [
                'HTTP_Authorization' => 'Bearer '.$this->login($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $user = $userRepository->findOneBy(['id' => $this->user->getId()]);
        $this->assertEquals($changeEmail, $user->getEmail());
        $this->assertTrue($user->isActive());
    }
}