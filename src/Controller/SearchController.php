<?php

namespace App\Controller;

use App\ElasticRepository\ArtistElasticRepository;
use App\Form\ArtistModelType;
use App\Model\ArtistModel;
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
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * SearchController constructor.
     * @param ArtistElasticRepository $artistElasticRepository
     * @param Paginator|PaginatorInterface $paginator
     */
    public function __construct(
        ArtistElasticRepository $artistElasticRepository,
        PaginatorInterface $paginator
    ) {
        $this->artistElasticRepository = $artistElasticRepository;
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

            return $this->normalize($results);
        }

        return $this->renderFormErrors($form);
    }

    public function postSearchEventsAction(Request $request)
    {

    }

    public function postSearchLocationsAction(Request $request)
    {

    }
}