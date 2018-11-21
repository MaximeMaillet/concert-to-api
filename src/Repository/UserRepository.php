<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getActiveUserFromEmail(string $email):? User
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%'.User::ROLE_USER.'%')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->andWhere('u.isActive = true')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getScrapperUser(string $token):? User
    {
        return $this->createQueryBuilder('u')
            ->where('MD5(CONCAT(u.salt, u.token)) = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
