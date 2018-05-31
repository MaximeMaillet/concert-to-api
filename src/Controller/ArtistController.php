<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\User;
use App\Form\ArtistType;
use App\Traits\SerializerTrait;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ArtistController
 * @package App\Controller
 */
class ArtistController extends FOSRestController
{
    use SerializerTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * ArtistController constructor.
     * @param EntityManagerInterface $entityManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Request $request
     * @param Artist $artist
     * @return array|bool|float|int|object|string
     */
    public function getArtistAction(Request $request, Artist $artist)
    {
        if (!$artist->isValidated()) {
            throw $this->createNotFoundException();
        }

        return $this->normalize($artist);
    }

    /**
     * @param Request $request
     * @return array|bool|float|int|object|string
     */
    public function getArtistsAction(Request $request)
    {
        $artists = $this->entityManager
            ->getRepository(Artist::class)
            ->findBy(['validated' => true])
        ;

        return $this->normalize($artists);
    }

    /**
     * @param Request $request
     * @param Artist $artist
     * @return array|bool|float|int|object|string|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function patchArtistAction(Request $request, Artist $artist)
    {
        if (!$this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ArtistType::class, $artist, ['method' => 'PATCH']);

        try {
            $form->submit($request->request->all());
        } catch(AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->normalize($artist);
        }

        return $this->renderFormErrors($form);
    }

    /**
     * @param Request $request
     * @return array|bool|float|int|object|string|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putArtistsAction(Request $request)
    {
        $artist = new Artist();
        $form = $this->createForm(ArtistType::class, $artist, ['method' => 'PATCH']);

        try {
            $form->submit($request->request->all());
        } catch(AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($artist);
            $this->entityManager->flush();
            return $this->normalize($artist);
        }

        return $this->renderFormErrors($form);
    }

    /**
     * @param Request $request
     * @param Artist $artist
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteArtistAction(Request $request, Artist $artist)
    {
        if (!$this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            throw $this->createAccessDeniedException();
        }

        $this->entityManager->remove($artist);
        $this->entityManager->flush();

        return $this->renderBoolean(true);
    }
}