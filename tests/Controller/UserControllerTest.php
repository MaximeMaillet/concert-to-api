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
        $this->credentials['email'] = 'maxime.maillet93+userControllerTest'.mt_rand().'@gmail.com';
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

        $users = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(is_array($users));
    }
}