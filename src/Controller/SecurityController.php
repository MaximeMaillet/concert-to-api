<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Services\UserService;
use App\Traits\SerializerTrait;
use FOS\RestBundle\Controller\FOSRestController;
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
     * SecurityController constructor.
     * @param UserService $userService
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        UserService $userService,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->userService = $userService;
        $this->passwordEncoder = $passwordEncoder;
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

        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $request->request->get('password'));

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $this->get('lexik_jwt_authentication.encoder')
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
            $user = $form->getData();
            $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->normalize($user);
        }

        return $this->renderFormErrors($form);
    }
}