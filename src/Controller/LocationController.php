<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\User;
use App\Form\LocationType;
use App\Traits\SerializerTrait;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class LocationController
 * @package App\Controller
 */
class LocationController extends FOSRestController
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
     * LocationController constructor.
     * @param $entityManager
     * @param $authorizationChecker
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getLocationAction(Request $request, Location $location)
    {
        if (!$this->authorizationChecker->isGranted(User::ROLE_ADMIN) && !$location->isValidated()) {
            throw $this->createNotFoundException();
        }

        return $this->serialize($location);
    }

    public function getLocationsAction(Request $request)
    {
        $locationRepository = $this->entityManager->getRepository(Location::class);

        if ($this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            return $this->serialize($locationRepository->findAll());
        } else {
            return $this->serialize($locationRepository->findBy(['validated' => true]));
        }
    }

    public function patchLocationAction(Request $request, Location $location)
    {
        if (!$this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(LocationType::class, $location, ['method' => 'PATCH']);

        try {
            $form->submit($request->request->all());
        } catch (AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->serialize($location);
        }

        return $this->renderFormErrors($form);
    }

    public function putLocationsAction(Request $request)
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location, ['method' => 'PUT']);

        try {
            $form->submit($request->request->all());
        } catch (AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($location);
            $this->entityManager->flush();
            return $this->serialize($location);
        }

        return $this->renderFormErrors($form);
    }

    public function deleteLocationAction(Request $request, Location $location)
    {
        if (!$this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            throw $this->createAccessDeniedException();
        }

        $this->entityManager->remove($location);
        $this->entityManager->flush();

        return $this->renderBoolean(true);
    }
}