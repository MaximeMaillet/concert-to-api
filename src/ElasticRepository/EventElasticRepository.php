<?php

namespace App\ElasticRepository;

use App\Model\EventModel;
use Elastica\Query;
use FOS\ElasticaBundle\Finder\TransformedFinder;

class EventElasticRepository
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

    public function searchEvent(EventModel $eventModel)
    {
        return $this->finder->createPaginatorAdapter(
            $this->getEvents($eventModel)
        );
    }

    public function getEvents(EventModel $eventModel)
    {
        $query = new Query();
        $mustQueries = [];
        $shouldQueries = [];

        if (null !== $eventModel->getName()) {
            $mustQueries = array_merge($mustQueries, [new Query\Match('name', $eventModel->getName())]);
        }

        if (null !== $eventModel->getStartDate()) {
            $startDateWithOneHour = new \DateTime($eventModel->getStartDate()->format(\DateTime::ATOM));
            $startDateWithOneHour->add(new \DateInterval('P1D'));
            $mustQueries = array_merge($mustQueries, [new Query\Range('startDate', [
                'gte' => $eventModel->getStartDate()->format(\DateTime::ATOM),
                'lte' => $startDateWithOneHour->format(\DateTime::ATOM),
            ])]);
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
        $query->setMinScore(0.5);
        return $query;
    }
}