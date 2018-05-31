<?php

namespace App\Tests\Utils;

use App\Entity\User;
use App\Normalizer\EventNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CustomWebTestCase extends WebTestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var User
     */
    protected $admin;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
        static::bootKernel();
    }

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
    }

    /**
     * @deprecated use getAuthorization instead of
     * @param User $user
     * @return string
     */
    protected function login(User $user)
    {
        /** @var JWTManager $jwtManager */
        $jwtManager = self::get('lexik_jwt_authentication.jwt_manager');

        return $jwtManager->create($user);
    }

    protected function getAuthorization(User $user)
    {
        /** @var JWTManager $jwtManager */
        $jwtManager = self::get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);

        return 'Bearer '.$token;
    }

    /**
     * @param $id
     * @return object
     */
    protected function get($id)
    {
        $kernel = $this->createKernel([
            'environment' => 'test'
        ]);
        $kernel->boot();
        return $kernel->getContainer()->get($id);
    }

    /**
     * @param $prefix
     * @param null $noRandom
     * @return string
     */
    protected function getCorrectEmail($prefix, $noRandom = null)
    {
        if (!$noRandom) {
            $noRandom = mt_rand();
        }
        $arEmail = explode('@', getenv('BASE_EMAIL_TO_SEND'));
        return $arEmail[0].'+'.$prefix.$noRandom.'@'.$arEmail[1];
    }

    /**
     * @param $json
     * @param $entity
     * @param array $groups
     * @return array
     */
    protected function jsonToEntity($json, $entity, $groups = [])
    {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([
            new EventNormalizer(),
            new ObjectNormalizer(),
        ], $encoders);

        $objectJson = json_decode($json);
        if (is_array($objectJson)) {
            $arrayReturn = [];
            foreach ($objectJson as $class) {
                $arrayReturn[] = $serializer->deserialize(json_encode($class), $entity, 'json');
            }

            return $arrayReturn;
        }

        return $serializer->deserialize($json, $entity, 'json', ['groups' => $groups]);
    }

}