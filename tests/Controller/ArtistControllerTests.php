<?php

namespace App\Tests\Controller;

use App\DataFixtures\ArtistsFixtures;
use App\Entity\Artist;
use App\Entity\User;
use App\Tests\Utils\CustomWebTestCase;
use App\Traits\SerializerTrait;
use Doctrine\ORM\EntityManagerInterface;

class ArtistControllerTests extends CustomWebTestCase
{
    use SerializerTrait;

    protected function setUp()
    {
        parent::setUp();
    }

    public function testGetArtistActionAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var Artist $artist */
        $artist = $entityManager->getRepository(Artist::class)
            ->findOneBy(['name' => ArtistsFixtures::ARTISTS_VALID_NAME[0]]);

        $client->request(
            'GET',
            self::get('router')->generate('get_artist', [
                'artist' => $artist->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        /** @var Artist $artistReturned */
        $artistReturned = $this->deserialize($client->getResponse()->getContent(), Artist::class);
        $this->assertEquals($artist->getName(), $artistReturned->getName());
    }

    public function testGetArtistNoValidActionAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var Artist $artist */
        $artist = $entityManager->getRepository(Artist::class)
            ->findOneBy(['name' => ArtistsFixtures::ARTISTS_NOVALID_NAME[0]]);

        $client->request(
            'GET',
            self::get('router')->generate('get_artist', [
                'artist' => $artist->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testGetArtistActionAsNotConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var Artist $artist */
        $artist = $entityManager->getRepository(Artist::class)
            ->findOneBy(['name' => ArtistsFixtures::ARTISTS_VALID_NAME[0]]);

        $client->request(
            'GET',
            self::get('router')->generate('get_artist', [
                'artist' => $artist->getId(),
            ]),
            [],
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testGetArtistsActionAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $artists = $entityManager
            ->getRepository(Artist::class)
            ->findBy(['validated' => true])
        ;

        $client->request(
            'GET',
            self::get('router')->generate('get_artists'),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(count($artists), count($response));
    }

    public function testGetArtistsActionAsNotConnected()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            self::get('router')->generate('get_artists'),
            [],
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testPatchArtistActionAsAdmin()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $artist = (new Artist())
            ->setName('test'.mt_rand())
        ;
        $entityManager->persist($artist);
        $entityManager->flush();

        $client->request(
            'PATCH',
            self::get('router')->generate('patch_artist', [
                'artist' => $artist->getId()
            ]),
            [
                'name' => 'NouveauName',
                'validated' => true,
            ],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        /** @var Artist $artistReturned */
        $artistReturned = $this->deserialize($client->getResponse()->getContent(), Artist::class);

        $this->assertEquals('NouveauName', $artistReturned->getName());
        $this->assertTrue($artistReturned->isValidated());
    }

    public function testPatchArtistActionAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var Artist $artist */
        $artist = $entityManager
            ->getRepository(Artist::class)
            ->findOneBy(['name' => ArtistsFixtures::ARTISTS_VALID_NAME[0]]);
        ;

        $client->request(
            'PATCH',
            self::get('router')->generate('patch_artist', [
                'artist' => $artist->getId()
            ]),
            [
                'name' => 'NouveauName',
            ],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testPatchArtistActionAsNotConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var Artist $artist */
        $artist = $entityManager
            ->getRepository(Artist::class)
            ->findOneBy(['name' => ArtistsFixtures::ARTISTS_VALID_NAME[0]]);
        ;

        $client->request(
            'PATCH',
            self::get('router')->generate('patch_artist', [
                'artist' => $artist->getId()
            ]),
            [
                'name' => 'NouveauName'
            ],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testPutArtistActionAsAdmin()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $client->request(
            'PUT',
            self::get('router')->generate('put_artists'),
            [
                'name' => 'NouveauName',
                'logo' => 'NewLogo',
                'validated' => true,
            ],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        /** @var Artist $artist */
        $artistReturned = $this->deserialize($client->getResponse()->getContent(), Artist::class);

        $this->assertEquals('NouveauName', $artistReturned->getName());
        $this->assertEquals('NewLogo', $artistReturned->getLogo());
        $this->assertTrue($artistReturned->isValidated());

        /** @var Artist $artist */
        $artist = $entityManager->getRepository(Artist::class)->findOneBy(['id' => $artistReturned->getId()]);
        $this->assertNotNull($artist);
        $this->assertEquals('NouveauName', $artist->getName());
        $this->assertEquals('NewLogo', $artist->getLogo());
        $this->assertTrue($artist->isValidated());
    }

    public function testPutArtistActionAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        $client->request(
            'PUT',
            self::get('router')->generate('put_artists'),
            [
                'name' => 'NouveauName',
                'logo' => 'NewLogo'
            ],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        /** @var Artist $artist */
        $artistReturned = $this->deserialize($client->getResponse()->getContent(), Artist::class);

        $this->assertEquals('NouveauName', $artistReturned->getName());
        $this->assertEquals('NewLogo', $artistReturned->getLogo());
        $this->assertFalse($artistReturned->isValidated());

        /** @var Artist $artist */
        $artist = $entityManager->getRepository(Artist::class)->findOneBy(['id' => $artistReturned->getId()]);
        $this->assertNotNull($artist);
        $this->assertEquals('NouveauName', $artist->getName());
        $this->assertEquals('NewLogo', $artist->getLogo());
        $this->assertFalse($artist->isValidated());
    }

    public function testPutArtistActionAsNotConnected()
    {
        $client = static::createClient();

        $client->request(
            'PUT',
            self::get('router')->generate('put_artists'),
            [
                'name' => 'NouveauName',
                'logo' => 'NewLogo'
            ],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testDeleteArtistActionAsAdmin()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $artist = (new Artist())->setName('ToDelete');
        $entityManager->persist($artist);
        $entityManager->flush();

        $client->request(
            'DELETE',
            self::get('router')->generate('delete_artist', [
                'artist' => $artist->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->admin)
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $artistFromBdd = $entityManager->getRepository(Artist::class)->findOneBy(['id' => $artist->getId()]);
        $this->assertNull($artistFromBdd);
    }

    public function testDeleteArtistActionAsConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        /** @var Artist $artist */
        $artist = $entityManager->getRepository(Artist::class)->findOneBy(['name' => ArtistsFixtures::ARTISTS_VALID_NAME[0]]);

        $client->request(
            'DELETE',
            self::get('router')->generate('delete_artist', [
                'artist' => $artist->getId(),
            ]),
            [],
            [],
            [
                'HTTP_Authorization' => $this->getAuthorization($this->user)
            ]
        );

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testDeleteArtistActionAsNotConnected()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        /** @var Artist $artist */
        $artist = $entityManager->getRepository(Artist::class)->findOneBy(['name' => ArtistsFixtures::ARTISTS_VALID_NAME[0]]);

        $client->request(
            'DELETE',
            self::get('router')->generate('delete_artist', [
                'artist' => $artist->getId(),
            ]),
            [],
            []
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
}