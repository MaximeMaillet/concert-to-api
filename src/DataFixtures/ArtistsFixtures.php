<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ArtistsFixtures extends Fixture
{
    const ARTISTS_VALID_NAME = [
        'Valid_MonArtistDeTest',
        'Valid_MonArtistDeTest2',
    ];

    const ARTISTS_NOVALID_NAME = [
        'NOValid_MonArtistDeTest',
        'NOValid_MonArtistDeTest2',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::ARTISTS_VALID_NAME as $item) {
            $artist = (new Artist())
                ->setName($item)
                ->setValidated(true)
            ;
            $manager->persist($artist);
        }

        foreach (self::ARTISTS_NOVALID_NAME as $item) {
            $artist = (new Artist())
                ->setName($item)
                ->setValidated(false)
            ;
            $manager->persist($artist);
        }

        $manager->flush();
    }
}