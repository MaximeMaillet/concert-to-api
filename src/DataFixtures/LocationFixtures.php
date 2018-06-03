<?php

namespace App\DataFixtures;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $location = (new Location())
            ->setName('MyLocation')
            ->setAddress('myloc')
            ->setPostalCode('69000')
            ->setCity('Lyon')
            ->setCountry('France')
            ->setLatitude(0.0)
            ->setLongitude(0.0)
            ->setValidated(true)
        ;

        $manager->persist($location);
        $manager->flush();
    }
}