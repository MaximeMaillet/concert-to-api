<?php

namespace App\Controller;

use App\ElasticRepository\ArtistElasticRepository;
use App\ElasticRepository\EventElasticRepository;
use App\ElasticRepository\LocationElasticRepository;
use App\Entity\User;
use App\Form\ArtistModelType;
use App\Form\EventModelType;
use App\Form\LocationModelType;
use App\Model\ArtistModel;
use App\Model\EventModel;
use App\Model\LocationModel;
use App\Services\ScrapperService;
use App\Traits\SerializerTrait;
use FOS\ElasticaBundle\Configuration\ManagerInterface;
use FOS\ElasticaBundle\Index\IndexManager;
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
     * @var IndexManager
     */
    protected $indexManager;

    /**
     * @var ScrapperService
     */
    protected $scrapperService;

    /**
     * SearchController constructor.
     * @param ArtistElasticRepository $artistElasticRepository
     * @param EventElasticRepository $eventElasticRepository
     * @param LocationElasticRepository $locationElasticRepository
     * @param Paginator|PaginatorInterface $paginator
     * @param IndexManager $indexManager
     * @param ScrapperService $scrapperService
     * @internal param EventElasticRepository $elasticRepository
     */
    public function __construct(
        ArtistElasticRepository $artistElasticRepository,
        EventElasticRepository $eventElasticRepository,
        LocationElasticRepository $locationElasticRepository,
        PaginatorInterface $paginator,
        IndexManager $indexManager,
        ScrapperService $scrapperService
    ) {
        $this->artistElasticRepository = $artistElasticRepository;
        $this->eventElasticRepository = $eventElasticRepository;
        $this->locationElasticRepository = $locationElasticRepository;
        $this->paginator = $paginator;
        $this->indexManager = $indexManager;
        $this->scrapperService = $scrapperService;
    }

    /**
     * @param Request $request
     * @return array|bool|float|int|object|string|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postSearchAction(Request $request)
    {
        $eventModel = new EventModel();
        $form = $this->createForm(EventModelType::class, $eventModel);

        try {
            $form->submit($request->request->all());
        } catch(AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $query = $this->eventElasticRepository->getEvents($eventModel);

            $search = $this->indexManager->getIndex('global')->createSearch($query);
            $search->addType('event');
            $search->addType('artist');
            $search->addType('location');

            $res = $search->search()->getResults();
            return $this->normalize($res);
        }

        return $this->renderFormErrors($form);
    }

    /**
     * @param Request $request
     * @return array|bool|float|int|object|string|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postSearchArtistsAction(Request $request)
    {
        $artistModel = new ArtistModel();
        $form = $this->createForm(ArtistModelType::class, $artistModel, ['method' => 'POST']);

        try {
            $form->submit($request->request->all());
        } catch(AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $summary = $this->artistElasticRepository->searchArtists($artistModel);
            $results = $this->paginator->paginate(
                $summary,
                $artistModel->getPage(),
                $artistModel->getLimit()
            );
            if(!$this->isGranted(User::ROLE_SCRAPPER)) {
                $this->scrapperService->scrapArtist($artistModel->getName());
            }

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
            if(!$this->isGranted(User::ROLE_SCRAPPER)) {
                $this->scrapperService->scrapEvent($eventModel->getName(), $eventModel->getStartDate());
            }

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