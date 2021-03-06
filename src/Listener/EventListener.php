<?php

namespace App\Listener;

use App\Entity\Event;
use Doctrine\ORM\Event\LifecycleEventArgs;

class EventListener
{
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Event) {
            $entity->setHash(md5($entity->getName().($entity->getStartDate())->format('dmY')));
        }
    }
}