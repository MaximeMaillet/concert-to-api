<?php

namespace App\Controller;

use App\ElasticRepository\ArtistElasticRepository;
use App\ElasticRepository\EventElasticRepository;
use App\ElasticRepository\LocationElasticRepository;
use App\Form\ArtistModelType;
use App\Form\EventModelType;
use App\Form\LocationModelType;
use App\Model\ArtistModel;
use App\Model\EventModel;
use App\Model\LocationModel;
use App\Traits\SerializerTrait;
use FOS\RestBundle\Controller\FOSRestController;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends FOSRestController
{
    use SerializerTrait;

    /**
     * @var ArtistElasticRepository
     */
    protected $artistElasticRepository;

    /**
     * @var EventElasticRepository
     */
    protected $eventElasticRepository;

    /**
     * @var LocationElasticRepository
     */
    protected $locationElasticRepository;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * SearchController constructor.
     * @param ArtistElasticRepository $artistElasticRepository
     * @param EventElasticRepository $eventElasticRepository
     * @param Paginator|PaginatorInterface $paginator
     * @internal param EventElasticRepository $elasticRepository
     */
    public function __construct(
        ArtistElasticRepository $artistElasticRepository,
        EventElasticRepository $eventElasticRepository,
        LocationElasticRepository $locationElasticRepository,
        PaginatorInterface $paginator
    ) {
        $this->artistElasticRepository = $artistElasticRepository;
        $this->eventElasticRepository = $eventElasticRepository;
        $this->locationElasticRepository = $locationElasticRepository;
        $this->paginator = $paginator;
    }

    /**
     * @param Request $request
     * @return array|bool|float|int|object|string|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postSearchArtistsAction(Request $request)
    {
        $artistModel = new ArtistModel();
        $form = $this->createForm(ArtistModelType::class, $artistModel);

        try {
            $form->submit($request->request->all());
        } catch(AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $summary = $this->artistElasticRepository->searchArtists($artistModel);
            $results = $this->paginator->paginate($summary);


            return $this->renderArray([
                'results' => $this->normalize($results),
                'pagination' => $results->getPaginationData()
            ]);
        }

        return $this->renderFormErrors($form);
    }

    /**
     * @param Request $request
     * @return array|bool|float|int|object|string|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postSearchEventsAction(Request $request)
    {
        $eventModel = new EventModel();
        $form = $this->createForm(EventModelType::class, $eventModel);

        try {
            $form->submit($request->request->all());
        } catch(AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $summary = $this->eventElasticRepository->searchEvent($eventModel);
            $results = $this->paginator->paginate($summary);

            return $this->renderArray([
                'results' => $this->normalize($results),
                'pagination' => $results->getPaginationData()
            ]);
        }

        return $this->renderFormErrors($form);
    }

    public function postSearchLocationsAction(Request $request)
    {
        $locationModel = new LocationModel();
        $form = $this->createForm(LocationModelType::class, $locationModel);

        try {
            $form->submit($request->request->all());
        } catch(AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $summary = $this->locationElasticRepository->search($locationModel);
            $results = $this->paginator->paginate($summary);

            return $this->renderArray([
                'results' => $this->normalize($results),
                'pagination' => $results->getPaginationData()
            ]);
        }

        return $this->renderFormErrors($form);
    }
}