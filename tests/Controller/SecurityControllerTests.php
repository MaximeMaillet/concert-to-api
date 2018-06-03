<?php
namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\Utils\CustomWebTestCase;
use App\Traits\SerializerTrait;
use Namshi\JOSE\JWS;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTests extends CustomWebTestCase
{
    use SerializerTrait;

    public function testLogin()
    {
        $email = getenv('USER_ADMIN_EMAIL');
        $password = getenv('USER_ADMIN_PASSWORD');
        $client = self::createClient();
        $container = $client->getContainer();

        $client->request(
            'POST',
            $container->get('router')->generate('post_login'),
            [
                'email' => $email,
                'password' => $password
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $content);

        $dataToken = JWS::load($content['token'])->getPayload();

        $this->assertTrue($dataToken['isActive']);
        $this->assertEquals($email, $dataToken['email']);
        $this->assertContains(User::ROLE_USER, $dataToken['roles']);
        $this->assertContains(User::ROLE_ADMIN, $dataToken['roles']);
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

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertNotNull($response['token']);
        $this->assertFalse($response['user']['isActive']);
        $this->assertEquals($email, $response['user']['email']);
        $this->assertContains(User::ROLE_USER, $response['user']['roles']);
    }
}