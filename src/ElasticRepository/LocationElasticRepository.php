<?php

namespace App\ElasticRepository;

use App\Model\LocationModel;
use Elastica\Query;
use FOS\ElasticaBundle\Finder\TransformedFinder;

/**
 * Class LocationElasticRepository
 * @package App\ElasticRepository
 */
class LocationElasticRepository
{
    /**
     * @var TransformedFinder
     */
    protected $finder;

    /**
     * ArtistElasticRepository constructor.
     * @param TransformedFinder $finder
     */
    public function __construct(TransformedFinder $finder)
    {
        $this->finder = $finder;
    }

    public function search(LocationModel $locationModel)
    {
        return $this->finder->createPaginatorAdapter(
            $this->getLocation($locationModel)
        );
    }

    public function getLocation(LocationModel $locationModel)
    {
        $query = new Query();
        $mustQueries = [];
        $shouldQueries = [];

        if (null !== $locationModel->getName()) {
            $mustQueries = array_merge($mustQueries, $this->addNameQuery($locationModel));
        }

        if (null !== $locationModel->getAddress()) {
            $mustQueries = array_merge($mustQueries, $this->addAddressQuery($locationModel));
        }

        if (null !== $locationModel->getCity()) {
            $mustQueries = array_merge($mustQueries, $this->addCityQuery($locationModel));
        }

        if (null !== $locationModel->getCountry()) {
            $mustQueries = array_merge($mustQueries, $this->addCountryQuery($locationModel));
        }

        if (null !== $locationModel->getPostalCode()) {
            $mustQueries = array_merge($mustQueries, $this->addPostalCodeQuery($locationModel));
        }

        if (null !== $locationModel->getLongitude() && null !== $locationModel->getLatitude()) {
            $shouldQueries = array_merge($shouldQueries, $this->addLatLngQuery($locationModel));
        }

        $boolQuery = new Query\BoolQuery();
        if (count($mustQueries) > 0) {
            $boolQuery->addMust($mustQueries);
        }

        if (count($shouldQueries) > 0) {
            $boolQuery->addShould($shouldQueries);
        }

        $query->setQuery($boolQuery);
        $query->addSort(['_score' => ['order' => 'desc']]);
        return $query;
    }

    public function addNameQuery(LocationModel $locationModel)
    {
        return [
            new Query\Match('name', $locationModel->getName())
        ];
    }

    public function addAddressQuery(LocationModel $locationModel)
    {
        return [
            new Query\Match('address', $locationModel->getAddress())
        ];
    }

    public function addCityQuery(LocationModel $locationModel)
    {
        return [
            new Query\Match('city', $locationModel->getCity())
        ];
    }

    public function addCountryQuery(LocationModel $locationModel)
    {
        return [
            new Query\Match('country', $locationModel->getCountry())
        ];
    }

    public function addPostalCodeQuery(LocationModel $locationModel)
    {
        return [
            new Query\Match('postal_code', $locationModel->getPostalCode())
        ];
    }

    public function addLatLngQuery(LocationModel $locationModel)
    {
        return [
            new Query\GeoDistance('location', [
                'lat' => $locationModel->getLatitude(),
                'lon' => $locationModel->getLongitude(),
            ], '5km')
        ];
    }
}