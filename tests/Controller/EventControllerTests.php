<?php

namespace App\Tests\Controller;

use App\DataFixtures\ArtistsFixtures;
use App\Entity\Artist;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use App\Tests\Utils\CustomWebTestCase;
use App\Traits\SerializerTrait;
use Doctrine\ORM\EntityManagerInterface;

class EventControllerTests extends CustomWebTestCase
{
    use SerializerTrait;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var Location
     */
    protected $location;

    protected function setUp()
    {
        parent::setUp();
        $entityManager = self::get('doctrine.orm.entity_manager');
        $this->location = $entityManager->getRepository(Location::class)->findOneBy(['validated' => true]);

        $this->event = (new Event())
            ->setName('MyEvent'.mt_rand())
            ->setLocation($this->location)
        ;
        $entityManager->persist($this->event);
        $entityManager->flush();
    }

    public function testGetEventAsConnected()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            self::get('router')->generate('get_event', [
                'event' => $this->event->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        /** @var Event $event */
        $event = $this->jsonToEntity($client->getResponse()->getContent(), Event::class);

        $this->assertEquals($this->event->getName(), $event->getName());
        $this->assertEquals($this->event->getStartDate()->format('dmyHi'), $event->getStartDate()->format('dmyHi'));
        $this->assertEquals($this->event->getHash(), $event->getHash());
    }

    public function testGetEventAsNotConnected()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            self::get('router')->generate('get_event', [
                'event' => $this->event->getId(),
            ]),
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testGetEventsAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $client->request(
            'GET',
            self::get('router')->generate('get_events'),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $events = json_decode($client->getResponse()->getContent(), true);
        $eventsTotal = $entityManager->getRepository(Event::class)->findAll();

        $this->assertEquals(count($eventsTotal), count($events));
    }

    public function testGetEventsAsNotConnected()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            self::get('router')->generate('get_events'),
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testPutEventsAsConnected()
    {
        $client = static::createClient();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $name = 'MyEventPut'.mt_rand();
        $dateStart = '2018-05-30T19:33:07+00:00';
        $hash = md5($name.(new \DateTime($dateStart))->format('dmY'));

        $location = $entityManager->getRepository(Location::class)->findOneBy(['validated' => true]);

        $client->request(
            'PUT',
            self::get('router')->generate('put_events'),
            [
                'name' => $name,
                'startDate' => $dateStart,
                'endDate' => '2018-05-31T19:33:07+00:00',
                'location' => $location->getId(),
            ],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        /** @var Event $event */
        $eventResponse = $this->jsonToEntity($client->getResponse()->getContent(), Event::class);

        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $eventResponse->getId()]);

        $this->assertEquals($name, $event->getName());
        $this->assertEquals((new \DateTime($dateStart))->format('dmYHi'), $event->getStartDate()->format('dmYHi'));
        $this->assertEquals($hash, $event->getHash());
    }

    public function testPutEventsAsNotConnected()
    {
        $client = static::createClient();
        $client->request(
            'PUT',
            self::get('router')->generate('put_events'),
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testPatchEventAsAdmin()
    {
        $client = static::createClient();
        $client->request(
            'PATCH',
            self::get('router')->generate('patch_event', [
                'event' => $this->event->getId(),
            ]),
            [
                'name' => 'OtherEvent',
                'startDate' => '2018-05-23T19:33:07+00:00',
                'endDate' => '2018-05-20T19:33:07+00:00',
                'location' => $this->event->getLocation()->getId(),
            ],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        /** @var Event $event */
        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $this->event->getId()]);

        $this->assertEquals('OtherEvent', $event->getName());
        $this->assertEquals('2018-05-23T19:33:07+00:00', $event->getStartDate()->format(\DateTime::ATOM));
        $this->assertEquals('2018-05-20T19:33:07+00:00', $event->getEndDate()->format(\DateTime::ATOM));

    }

    public function testPatchEventAsConnected()
    {
        $client = static::createClient();
        $client->request(
            'PATCH',
            self::get('router')->generate('patch_event', [
                'event' => $this->event->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testPatchEventAsNotConnected()
    {
        $client = static::createClient();
        $client->request(
            'PATCH',
            self::get('router')->generate('patch_event', [
                'event' => $this->event->getId(),
            ]),
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testDeleteEventAsAdmin()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $event = (new Event())
            ->setName('Mydeletetevent')
            ->setStartDate(new \DateTime('now'))
            ->setLocation($this->location)
        ;
        $entityManager->persist($event);
        $entityManager->flush();

        $client->request(
            'DELETE',
            self::get('router')->generate('delete_event', [
                'event' => $event->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $eventRemove = $entityManager->getRepository(Event::class)->findOneBy(['id' => $event->getId()]);
        $this->assertNull($eventRemove);
    }

    public function testDeleteEventAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $event = (new Event())
            ->setName('Mydeletetevent')
            ->setStartDate(new \DateTime('now'))
            ->setLocation($this->location)
        ;
        $entityManager->persist($event);
        $entityManager->flush();

        $client->request(
            'DELETE',
            self::get('router')->generate('delete_event', [
                'event' => $event->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testDeleteEventAsNotConnected()
    {
        $client = static::createClient();
        $client->request(
            'DELETE',
            self::get('router')->generate('delete_event', [
                'event' => $this->event->getId(),
            ]),
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
}