<?php

namespace App\ElasticRepository;

use App\Entity\User;
use App\Model\EventModel;
use Elastica\Query;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventElasticRepository
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
     * @internal param TransformedFinder $finder
     */
    public function __construct(
        TransformedFinder $appFinder,
        TransformedFinder $scrapperFinder,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->appFinder = $appFinder;
        $this->scrapperFinder = $scrapperFinder;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param EventModel $eventModel
     * @return mixed
     */
    public function searchEvent(EventModel $eventModel)
    {
        if ($this->authorizationChecker->isGranted(User::ROLE_SCRAPPER)) {
            return $this->scrapperFinder->createPaginatorAdapter(
                $this->getEvents($eventModel)
            );
        }

        return $this->appFinder->createPaginatorAdapter(
            $this->getEvents($eventModel)
        );
    }

    public function getEvents(EventModel $eventModel)
    {
        $query = new Query();
        $boolQuery = new Query\BoolQuery();

        if ($this->authorizationChecker->isGranted(User::ROLE_SCRAPPER)) {
            $this->addScrapperQueries($boolQuery, $eventModel);
        } else {
            $this->addUserQueries($boolQuery, $eventModel);
        }

        $query->setQuery($boolQuery);
        $query->addSort(['_score' => ['order' => 'desc']]);
        $query->setMinScore(0.5);
        return $query;
    }

    /**
     * @param Query\BoolQuery $query
     * @param EventModel $eventModel
     */
    protected function addScrapperQueries(Query\BoolQuery $query, EventModel $eventModel)
    {
        if(null !== $eventModel->getHash()) {
            $query->addMust(new Query\Term(['hash' => $eventModel->getHash()]));
        } else {
            if (null !== $eventModel->getName()) {
                if ($eventModel->isExact()) {
                    $query->addMust(new Query\Term(['name' => $eventModel->getName()]));
                } else {
                    $query->addMust(new Query\Match('name', $eventModel->getName()));
                }
            }

            if (null !== $eventModel->getStartDate()) {
                $startDateWithOneHour = new \DateTime($eventModel->getStartDate()->format(\DateTime::ATOM));
                $startDateWithOneHour->add(new \DateInterval('P1D'));
                $query->addMust(new Query\Range('startDate', [
                    'gte' => $eventModel->getStartDate()->format(\DateTime::ATOM),
                    'lte' => $startDateWithOneHour->format(\DateTime::ATOM),
                ]));
            }
        }
    }

    /**
     * @param Query\BoolQuery $query
     * @param EventModel $eventModel
     */
    protected function addUserQueries(Query\BoolQuery $query, EventModel $eventModel)
    {
        if (null !== $eventModel->getName()) {
            $query->addMust(new Query\Match('name', $eventModel->getName()));
        }

        if (null !== $eventModel->getStartDate()) {
            $startDateWithOneHour = new \DateTime($eventModel->getStartDate()->format(\DateTime::ATOM));
            $startDateWithOneHour->add(new \DateInterval('P1D'));
            $query->addMust(new Query\Range('startDate', [
                'gte' => $eventModel->getStartDate()->format(\DateTime::ATOM),
                'lte' => $startDateWithOneHour->format(\DateTime::ATOM),
            ]));
        }
    }
}