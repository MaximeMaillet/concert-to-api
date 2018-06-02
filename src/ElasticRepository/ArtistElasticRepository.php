<?php

namespace App\ElasticRepository;

use App\Model\ArtistModel;
use Elastica\Query;
use FOS\ElasticaBundle\Finder\TransformedFinder;

class ArtistElasticRepository
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

    public function searchArtists(ArtistModel $artistModel)
    {
        return $this->finder->createPaginatorAdapter(
            $this->getArtists($artistModel)
        );
    }

    protected function getArtists(ArtistModel $artistModel)
    {
        $query = new Query();
        $mustQueries = [];
        $shouldQueries = [];

        $mustQueries = array_merge($mustQueries, $this->addBaseQueries($artistModel));

        if (null !== $artistModel->getExactName()) {
            $mustQueries = array_merge($mustQueries, [new Query\Match('name', $artistModel->getExactName())]);
        }

        if (null !== $artistModel->getName()) {
            $shouldQueries = array_merge($shouldQueries, [new Query\Match('name', $artistModel->getName())]);
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

    /**
     * @param ArtistModel $artistModel
     * @return array
     */
    protected function addBaseQueries(ArtistModel $artistModel)
    {
        $queries = [];
        if (!$artistModel->isFromScrapper()) {
            $queries[] = new Query\Term(['validated' => $artistModel->isValidated()]);
        }

        return $queries;
    }
}