<?php
/**
 * Created by PhpStorm.
 * User: MaximeMaillet
 * Date: 30/05/2018
 * Time: 20:03
 */

namespace App\Controller;


use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Traits\SerializerTrait;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventController extends FOSRestController
{
    use SerializerTrait;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * EventController constructor.
     * @param $authorizationChecker
     * @param $entityManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerInterface $entityManager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     * @param Event $event
     * @return array|bool|float|int|object|string
     */
    public function getEventAction(Request $request, Event $event)
    {
        return $this->serialize($event);
    }

    /**
     * @param Request $request
     * @return array|bool|float|int|object|string
     */
    public function getEventsAction(Request $request)
    {
        return $this->serialize(
            $this->entityManager
                ->getRepository(Event::class)
                ->findAll()
        );
    }

    /**
     * @param Request $request
     * @return array|object|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function putEventsAction(Request $request)
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event, ['method' => 'PUT']);

        try {
            $form->submit($request->request->all());
        } catch (AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($event);
            $this->entityManager->flush();

            return $this->serialize($event);
        }

        return $this->renderFormErrors($form);
    }

    /**
     * @param Request $request
     * @param Event $event
     * @return array|object|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function patchEventAction(Request $request, Event $event)
    {
        if (!$this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(EventType::class, $event, ['method' => 'PATCH']);

        try {
            $form->submit($request->request->all());
        } catch (AlreadySubmittedException $e) {
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->serialize($event);
        }

        return $this->renderFormErrors($form);
    }

    /**
     * @param Request $request
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteEventAction(Request $request, Event $event)
    {
        if (!$this->authorizationChecker->isGranted(User::ROLE_ADMIN)) {
            throw $this->createAccessDeniedException();
        }

        $this->entityManager->remove($event);
        $this->entityManager->flush();

        return $this->renderBoolean(true);
    }
}