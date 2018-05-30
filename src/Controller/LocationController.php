<?php

namespace App\Controller;

use App\Entity\Location;
use App\Traits\SerializerTrait;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
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

    }

    public function getLocationsAction(Request $request)
    {

    }

    public function patchLocationAction(Request $request, Location $location)
    {

    }

    public function putLocationsAction(Request $request)
    {

    }

    public function deleteLocationAction(Request $request, Location $location)
    {

    }
}