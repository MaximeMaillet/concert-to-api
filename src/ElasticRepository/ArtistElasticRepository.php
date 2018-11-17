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

    /**
     * @param ArtistModel $artistModel
     * @return \FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface|\FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter
     */
    public function searchArtists(ArtistModel $artistModel)
    {
        return $this->finder->createPaginatorAdapter(
            $this->getArtists($artistModel)
        );
    }

    /**
     * @param ArtistModel $artistModel
     * @return Query
     */
    protected function getArtists(ArtistModel $artistModel)
    {
        $query = new Query();
        $boolQuery = new Query\BoolQuery();

        $this->addBaseQueries($boolQuery, $artistModel);

        $query->setQuery($boolQuery);
        $query->addSort(['_score' => ['order' => 'desc']]);
        return $query;
    }

    /**
     * @param Query|Query\BoolQuery $query
     * @param ArtistModel $artistModel
     */
    protected function addBaseQueries(Query\BoolQuery $query, ArtistModel $artistModel)
    {
        if (null !== $artistModel->getName()) {
            $query->addMust(new Query\Match('name', $artistModel->getName()));
        }
    }
}
