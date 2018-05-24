<?php
/**
 * Created by PhpStorm.
 * User: MaximeMaillet
 * Date: 16/05/2018
 * Time: 21:17
 */

namespace App\Services;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserService
{
    /**
     * @var PasswordEncoder
     */
    protected $passwordEncoder;

    /**
     * UserService constructor.
     * @param PasswordEncoderInterface $passwordEncoder
     */
    public function __construct()
    {
//        $this->passwordEncoder = $passwordEncoder;
    }

    public function createUser(User $user)
    {
        if (!empty($user->getPlainPassword() !== null)) {
//            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
        }

        return $user;
    }
}