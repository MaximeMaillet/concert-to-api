<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Router;

class SecurityControllerTests extends WebTestCase
{
    public function testLogin()
    {

    }

    public function testRegistration()
    {
        $client = self::createClient();
        $container = $client->getContainer();

        $client->request(
            'POST',
            $container->get('router')->generate('post_register'),
            [
                'email' => 'maxime.maillet+concertotests'.mt_rand().'@gmail.com',
                'plainPassword' => 'MySecurPassw0rd'
            ]
        );
        dump($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}