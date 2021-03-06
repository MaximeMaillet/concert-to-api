<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtistRepository")
 * @ORM\Table(name="artists")
 */
class Artist
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Groups({"auth", "noauth"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=false, nullable=false)
     * @Assert\NotBlank()
     * @Groups({"auth"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"auth"})
     */
    protected $logo;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"auth"})
     */
    protected $validated;

    /**
     * @var Event[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Event", inversedBy="artists", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="artists_events",
     *      joinColumns={@ORM\JoinColumn(name="artist_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="cascade")}
     * )
     * @Groups({"auth"})
     */
    protected $events;

    /**
     * Artist constructor.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->validated = false;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Artist
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Artist
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param mixed $logo
     * @return Artist
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * For ES
     * @return int
     */
    public function getCountEvents()
    {
        return $this->events->count();
    }

    /**
     * @param mixed $events
     * @return Artist
     */
    public function setEvents($events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * @param Event $event
     * @return $this
     */
    public function addEvent($event)
    {
        $isContain = false;
        $eventArray = $this->events->toArray();
        /** @var Event $i */
        for($i=0;$i<count($eventArray); $i++) {
            if($eventArray[$i]->getHash() === $event->getHash()) {
                $isContain = true;
            }
        }

        if (!$isContain) {
            $this->events->add($event);
        }

        return $this;
    }

    /**
     * @param Event $event
     * @return $this
     */
    public function removeEvent(Event $event)
    {
        $isContain = false;
        $eventArray = $this->events->toArray();
        /** @var Event $i */
        for($i=0;$i<count($eventArray); $i++) {
            if($eventArray[$i]->getHash() === $event->getHash()) {
//                $isContain = true;
            }
        }

        if ($isContain) {
//            $this->events->removeElement($event);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * @param mixed $validated
     * @return $this
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
        return $this;
    }
}