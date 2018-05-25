<?php
namespace App\Tests\Controller;

use App\Entity\User;
use App\Traits\SerializerTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Router;

class SecurityControllerTests extends WebTestCase
{
    use SerializerTrait;

    public function testLogin()
    {

    }

    public function testRegistration()
    {
        $email = 'maxime.maillet+concertotests'.mt_rand().'@gmail.com';
        $client = self::createClient();
        $container = $client->getContainer();

        $client->request(
            'POST',
            $container->get('router')->generate('post_register'),
            [
                'email' => $email,
                'plainPassword' => 'MySecurPassw0rd'
            ]
        );

        /** @var User $user */
        $user = $this->deserialize($client->getResponse()->getContent(), User::class, ['auth']);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertFalse($user->isActive());
        $this->assertEquals($email, $user->getEmail());
        $this->assertContains(User::ROLE_USER, $user->getRoles());
    }
}