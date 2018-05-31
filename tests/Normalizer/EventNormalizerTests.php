<?php

namespace App\Tests\Normalizer;

use App\Entity\Artist;
use App\Entity\Event;
use App\Entity\Location;
use App\Tests\Utils\CustomWebTestCase;
use App\Traits\SerializerTrait;
use Doctrine\ORM\EntityManagerInterface;

class EventNormalizerTests extends CustomWebTestCase
{
    use SerializerTrait;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var Artist
     */
    protected $artist;

    /**
     * @var Location
     */
    protected $location;

    /**
     * @return \App\Entity\User
     */
    protected function getUser()
    {
        return $this->user;
    }

    protected function setUp()
    {
        parent::setUp();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::get('doctrine.orm.entity_manager');

        $dateStart = new \DateTime('now');
        $dateEnd = new \DateTime('now');

        $this->artist = (new Artist())
            ->setName('MonArtistToNormalize')
            ->setValidated(true)
            ->setLogo('MonLogo')
        ;

        $entityManager->persist($this->artist);

        $this->location = (new Location())
            ->setName('MyLocationToNormalize')
            ->setLongitude(10.0)
            ->setLatitude(10.0)
            ->setValidated(true)
            ->setPostalCode('69000')
            ->setCountry('France')
            ->setCity('Lyon')
            ->setAddress('12 rue')
        ;

        $entityManager->persist($this->location);
        $entityManager->flush();

        $this->event = (new Event())
            ->setName('MonEventToNormalize')
            ->setDateStart($dateStart)
            ->setDateEnd($dateEnd)
            ->addArtist($this->artist)
            ->setLocation($this->location)
        ;

        $this->artist->addEvent($this->event);

        $entityManager->persist($this->event);
        $entityManager->flush();
    }

    public function testNormalize()
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::get('doctrine.orm.entity_manager');

        $event = $entityManager->getRepository(Event::class)->findOneBy(['id' => $this->event->getId()]);
        $arrayEvent = $this->normalize($event);

        $this->assertArrayHasKey('id', $arrayEvent);
        $this->assertArrayHasKey('name', $arrayEvent);
        $this->assertArrayHasKey('location', $arrayEvent);
        $this->assertArrayHasKey('artists', $arrayEvent);
        $this->assertArrayHasKey('dateStart', $arrayEvent);
        $this->assertArrayHasKey('dateEnd', $arrayEvent);
    }
}