<?php

namespace App\Tests\Controller;

use App\Entity\Location;
use App\Entity\User;
use App\Tests\Utils\CustomWebTestCase;
use App\Traits\SerializerTrait;
use Doctrine\ORM\EntityManagerInterface;

class LocationControllerTests extends CustomWebTestCase
{
    use SerializerTrait;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var User
     */
    protected $admin;

    /**
     * @var Location
     */
    protected $location;

    protected function setUp()
    {
        parent::setUp();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::get('doctrine.orm.entity_manager');
        $this->admin = $entityManager->getRepository(User::class)
            ->findOneBy(['email' => getenv('USER_ADMIN_EMAIL')]);

        $this->user = $entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles NOT LIKE :roles')
            ->setParameter('roles', '%ROLE_ADMIN%')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;

        $this->location = (new Location())
            ->setName('MyLoc'.mt_rand())
            ->setCity('City'.mt_rand())
            ->setAddress('Address'.mt_rand())
            ->setCountry('Country'.mt_rand())
            ->setLatitude(90.2)
            ->setLongitude(8.2)
            ->setPostalCode('51700')
        ;
    }

    protected function checkLocationAssert($content, Location $exactLocation)
    {
        /** @var Location $location */
        $location = $this->deserialize($content, Location::class);

        $this->assertEquals($exactLocation->getName(), $location->getName());
        $this->assertEquals($exactLocation->getAddress(), $location->getAddress());
        $this->assertEquals($exactLocation->getCity(), $location->getCity());
        $this->assertEquals($exactLocation->getCountry(), $location->getCountry());
        $this->assertEquals($exactLocation->getPostalCode(), $location->getPostalCode());
        $this->assertEquals($exactLocation->getLatitude(), $location->getLatitude());
        $this->assertEquals($exactLocation->getLongitude(), $location->getLongitude());
        $this->assertEquals($exactLocation->isValidated(), $location->isValidated());
    }

    public function testGetValidLocationAsAdmin()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $this->location->setValidated(true);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'GET',
            self::get('router')->generate('get_location', [
                'location' => $this->location->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->checkLocationAssert($client->getResponse()->getContent(), $this->location);
    }

    public function testGetValidLocationAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $this->location->setValidated(true);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'GET',
            self::get('router')->generate('get_location', [
                'location' => $this->location->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->checkLocationAssert($client->getResponse()->getContent(), $this->location);
    }

    public function testGetValidLocationAsNotConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $this->location->setValidated(true);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'GET',
            self::get('router')->generate('get_location', [
                'location' => $this->location->getId(),
            ]),
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testGetNoValidLocationAsAdmin()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $this->location->setValidated(false);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'GET',
            self::get('router')->generate('get_location', [
                'location' => $this->location->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        /** @var Location $location */
        $location = $this->deserialize($client->getResponse()->getContent(), Location::class);

        $this->assertEquals($this->location->getName(), $location->getName());
        $this->assertEquals($this->location->getAddress(), $location->getAddress());
        $this->assertEquals($this->location->getCity(), $location->getCity());
        $this->assertEquals($this->location->getCountry(), $location->getCountry());
        $this->assertEquals($this->location->getPostalCode(), $location->getPostalCode());
        $this->assertEquals($this->location->getLatitude(), $location->getLatitude());
        $this->assertEquals($this->location->getLongitude(), $location->getLongitude());
        $this->assertFalse($location->isValidated());
    }

    public function testGetNoValidLocationAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $this->location->setValidated(false);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'GET',
            self::get('router')->generate('get_location', [
                'location' => $this->location->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testGetNoValidLocationAsNotConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $this->location->setValidated(false);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'GET',
            self::get('router')->generate('get_location', [
                'location' => $this->location->getId(),
            ]),
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testGetLocationsAsAdmin()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $this->location->setValidated(false);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'GET',
            self::get('router')->generate('get_locations'),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $locations = $entityManager->getRepository(Location::class)->findAll();
        $locationsReturned = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(count($locations), count($locationsReturned));
    }

    public function testGetLocationsAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $this->location->setValidated(false);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'GET',
            self::get('router')->generate('get_locations'),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $locations = $entityManager->getRepository(Location::class)->findBy(['validated' => true]);
        $locationsReturned = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(count($locations), count($locationsReturned));
    }

    public function testGetLocationsAsNotConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $this->location->setValidated(false);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'GET',
            self::get('router')->generate('get_locations'),
            [],
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testPatchLocationAsAdmin()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $this->location->setValidated(false);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $locationToCheck = (new Location())
            ->setName('MyLoc'.mt_rand())
            ->setCity('City'.mt_rand())
            ->setAddress('Address'.mt_rand())
            ->setCountry('Country'.mt_rand())
            ->setLatitude(90.2)
            ->setLongitude(8.2)
            ->setPostalCode('51700')
            ->setValidated(true)
        ;

        $client->request(
            'PATCH',
            self::get('router')->generate('patch_location', [
                'location' => $this->location->getId()
            ]),
            [
                'name' => $locationToCheck->getName(),
                'address' => $locationToCheck->getAddress(),
                'city' => $locationToCheck->getCity(),
                'postalCode' => $locationToCheck->getPostalCode(),
                'country' => $locationToCheck->getCountry(),
                'latitude' => $locationToCheck->getLatitude(),
                'longitude' => $locationToCheck->getLongitude(),
                'validated' => $locationToCheck->isValidated(),
            ],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->checkLocationAssert($client->getResponse()->getContent(), $locationToCheck);
    }

    public function testPatchLocationAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $this->location->setValidated(false);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $locationToCheck = (new Location())
            ->setName('MyLoc'.mt_rand())
            ->setCity('City'.mt_rand())
            ->setAddress('Address'.mt_rand())
            ->setCountry('Country'.mt_rand())
            ->setLatitude(90.2)
            ->setLongitude(8.2)
            ->setPostalCode('51700')
            ->setValidated(true)
        ;

        $client->request(
            'PATCH',
            self::get('router')->generate('patch_location', [
                'location' => $this->location->getId()
            ]),
            [
                'name' => $locationToCheck->getName(),
                'address' => $locationToCheck->getAddress(),
                'city' => $locationToCheck->getCity(),
                'postalCode' => $locationToCheck->getPostalCode(),
                'country' => $locationToCheck->getCountry(),
                'latitude' => $locationToCheck->getLatitude(),
                'longitude' => $locationToCheck->getLongitude(),
                'validated' => $locationToCheck->isValidated(),
            ],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testPatchLocationAsNotConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $this->location->setValidated(false);
        $entityManager->persist($this->location);
        $entityManager->flush();

        $locationToCheck = (new Location())
            ->setName('MyLoc'.mt_rand())
            ->setCity('City'.mt_rand())
            ->setAddress('Address'.mt_rand())
            ->setCountry('Country'.mt_rand())
            ->setLatitude(90.2)
            ->setLongitude(8.2)
            ->setPostalCode('51700')
            ->setValidated(true)
        ;

        $client->request(
            'PATCH',
            self::get('router')->generate('patch_location', [
                'location' => $this->location->getId()
            ]),
            [
                'name' => $locationToCheck->getName(),
                'address' => $locationToCheck->getAddress(),
                'city' => $locationToCheck->getCity(),
                'postalCode' => $locationToCheck->getPostalCode(),
                'country' => $locationToCheck->getCountry(),
                'latitude' => $locationToCheck->getLatitude(),
                'longitude' => $locationToCheck->getLongitude(),
                'validated' => $locationToCheck->isValidated(),
            ],
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testPutLocationAsAdmin()
    {
        $client = static::createClient();

        $locationToCheck = (new Location())
            ->setName('MyLoc'.mt_rand())
            ->setCity('City'.mt_rand())
            ->setAddress('Address'.mt_rand())
            ->setCountry('Country'.mt_rand())
            ->setLatitude(90.2)
            ->setLongitude(8.2)
            ->setPostalCode('51700')
            ->setValidated(true)
        ;

        $client->request(
            'PUT',
            self::get('router')->generate('put_locations'),
            [
                'name' => $locationToCheck->getName(),
                'address' => $locationToCheck->getAddress(),
                'city' => $locationToCheck->getCity(),
                'country' => $locationToCheck->getCountry(),
                'postalCode' => $locationToCheck->getPostalCode(),
                'latitude' => $locationToCheck->getLatitude(),
                'longitude' => $locationToCheck->getLongitude(),
                'validated' => $locationToCheck->isValidated(),
            ],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->checkLocationAssert($client->getResponse()->getContent(), $locationToCheck);
    }

    public function testPutLocationAsConnected()
    {
        $client = static::createClient();

        $locationToCheck = (new Location())
            ->setName('MyLoc'.mt_rand())
            ->setCity('City'.mt_rand())
            ->setAddress('Address'.mt_rand())
            ->setCountry('Country'.mt_rand())
            ->setLatitude(90.2)
            ->setLongitude(8.2)
            ->setPostalCode('51700')
        ;

        $client->request(
            'PUT',
            self::get('router')->generate('put_locations'),
            [
                'name' => $locationToCheck->getName(),
                'address' => $locationToCheck->getAddress(),
                'city' => $locationToCheck->getCity(),
                'postalCode' => $locationToCheck->getPostalCode(),
                'country' => $locationToCheck->getCountry(),
                'latitude' => $locationToCheck->getLatitude(),
                'longitude' => $locationToCheck->getLongitude(),
            ],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->checkLocationAssert($client->getResponse()->getContent(), $locationToCheck);
    }

    public function testPutLocationAsNotConnected()
    {
        $client = static::createClient();

        $locationToCheck = (new Location())
            ->setName('MyLoc'.mt_rand())
            ->setCity('City'.mt_rand())
            ->setAddress('Address'.mt_rand())
            ->setCountry('Country'.mt_rand())
            ->setLatitude(90.2)
            ->setLongitude(8.2)
            ->setPostalCode('51700')
            ->setValidated(true)
        ;

        $client->request(
            'PUT',
            self::get('router')->generate('put_locations'),
            [
                'name' => $locationToCheck->getName(),
                'address' => $locationToCheck->getAddress(),
                'city' => $locationToCheck->getCity(),
                'country' => $locationToCheck->getCountry(),
                'latitude' => $locationToCheck->getLatitude(),
                'longitude' => $locationToCheck->getLongitude(),
                'validated' => $locationToCheck->isValidated(),
            ],
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testDeleteLocationAsAdmin()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'DELETE',
            self::get('router')->generate('delete_location', [
                'location' => $this->location->getId()
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        /** @var Location $location */
        $location = $entityManager->getRepository(Location::class)->findOneBy(['id' => $this->location->getId()]);
        $this->assertNull($location);
    }

    public function testDeleteLocationAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'DELETE',
            self::get('router')->generate('delete_location', [
                'location' => $this->location->getId()
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testDeleteLocationAsNotConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($this->location);
        $entityManager->flush();

        $client->request(
            'DELETE',
            self::get('router')->generate('delete_location', [
                'location' => $this->location->getId()
            ]),
            [],
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
}