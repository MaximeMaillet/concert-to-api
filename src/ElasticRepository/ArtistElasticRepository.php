<?php

namespace App\ElasticRepository;

use App\Entity\Artist;
use App\Entity\User;
use App\Model\ArtistModel;
use Elastica\Query;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ArtistElasticRepository
{
    /**
     * @var TransformedFinder
     */
    protected $appFinder;

    /**
     * @var TransformedFinder
     */
    protected $scrapperFinder;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * ArtistElasticRepository constructor.
     * @param TransformedFinder $appFinder
     * @param TransformedFinder $scrapperFinder
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        TransformedFinder $appFinder,
        TransformedFinder $scrapperFinder,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->appFinder = $appFinder;
        $this->scrapperFinder =$scrapperFinder;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param ArtistModel $artistModel
     * @return \FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface|\FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter
     */
    public function searchArtists(ArtistModel $artistModel)
    {
        if($this->authorizationChecker->isGranted(User::ROLE_SCRAPPER)) {
            return $this->scrapperFinder->createPaginatorAdapter(
                $this->getArtists($artistModel)
            );
        } else {
            return $this->appFinder->createPaginatorAdapter(
                $this->getArtists($artistModel)
            );
        }
    }

    /**
     * @param ArtistModel $artistModel
     * @return Query
     */
    protected function getArtists(ArtistModel $artistModel)
    {
        $query = new Query();
        $boolQuery = new Query\BoolQuery();

        if ($this->authorizationChecker->isGranted(User::ROLE_SCRAPPER)) {
            $this->addScrapperQueries($boolQuery, $artistModel);
        } elseif ($this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            $this->addAdminQueries($boolQuery, $artistModel);
        } else {
            $this->addUserQueries($boolQuery, $artistModel);
        }

        $query->setQuery($boolQuery);
        $query->setFrom($artistModel->getPage());
        $query->addSort([
            '_score' => ['order' => 'desc'],
            'count_events' => ['order' => 'desc']
        ]);
        return $query;
    }

    /**
     * @param Query|Query\BoolQuery $query
     * @param ArtistModel $artistModel
     */
    protected function addAdminQueries(Query\BoolQuery $query, ArtistModel $artistModel)
    {
        if (null !== $artistModel->getName()) {
            $query->addMust(
                (new Query\MatchPhrase('name', $artistModel->getName()))
            );
        }
    }

    /**
     * @param Query\BoolQuery $query
     * @param ArtistModel $artistModel
     */
    protected function addUserQueries(Query\BoolQuery $query, ArtistModel $artistModel)
    {
        $query->addMust(new Query\Term(['validated' => true]));
        if (null !== $artistModel->getName()) {
            $query->addMust(
                (new Query\MatchPhrase('name', $artistModel->getName()))
            );
        }
    }

    /**
     * @param Query\BoolQuery $query
     * @param ArtistModel $artistModel
     */
    protected function addScrapperQueries(Query\BoolQuery $query, ArtistModel $artistModel)
    {
        if (null === $artistModel->getName()) {
            return;
        }

        if($artistModel->isExact()) {
            $query->addMust(new Query\Term(['name' => $artistModel->getName()]));
        } else {
            $query->addMust(new Query\Match('name', $artistModel->getName()));
        }
    }
}
