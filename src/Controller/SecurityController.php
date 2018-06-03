<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Services\UserService;
use App\Traits\SerializerTrait;
use FOS\RestBundle\Controller\FOSRestController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends FOSRestController
{
    use SerializerTrait;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * @var JWTEncoderInterface
     */
    protected $encoder;

    /**
     * SecurityController constructor.
     * @param UserService $userService
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param JWTEncoderInterface $encoder
     */
    public function __construct(
        UserService $userService,
        UserPasswordEncoderInterface $passwordEncoder,
        JWTEncoderInterface $encoder
    ) {
        $this->userService = $userService;
        $this->passwordEncoder = $passwordEncoder;
        $this->encoder = $encoder;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function postLoginAction(Request $request)
    {
        $form = $this->createForm(UserType::class, null, ['method' => 'POST',]);
        try {
            $form->submit($request->request->all(), true);
        } catch(AlreadySubmittedException $e) {
        }

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->getActiveUserFromEmail($form->getData()->getEmail());

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isValid = $this->passwordEncoder
            ->isPasswordValid($user, $request->request->get('password'));

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $this->encoder
            ->encode(
                array_merge(
                    $this->normalize($user, ['auth']),
                    ['exp' => time() + 3600]
                )
            );

        return new JsonResponse(['token' => $token]);
    }

    /**
     * @param Request $request
     * @return mixed|JsonResponse
     */
    public function postRegisterAction(Request $request)
    {
        $form = $this->createForm(UserType::class, null, [
            'method' => 'POST',
            'creation' => true,
        ]);

        try {
            $form->submit($request->request->all(), true);
        } catch(AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            //@todo
            //$user->addRole(User::ROLE_ADMIN);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $token = $this->encoder
                ->encode(
                    array_merge(
                        $this->normalize($user, ['auth']),
                        ['exp' => time() + 3600]
                    )
                );

            return new JsonResponse([
                'token' => $token,
                'user' => $this->normalize($user)
            ]);
        }

        return $this->renderFormErrors($form);
    }
}