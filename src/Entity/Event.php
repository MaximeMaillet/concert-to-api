<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\Table(name="events")
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Groups({"auth"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Groups({"auth"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"auth"})
     */
    protected $hash;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Groups({"auth"})
     */
    protected $dateStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"auth"})
     */
    protected $dateEnd;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", fetch="EAGER")
     * @ORM\JoinColumn(name="location_id", nullable=true, onDelete="SET NULL", referencedColumnName="id")
     * @Groups({"auth"})
     */
    protected $location;

    /**
     * @var ArrayCollection|Artist[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Artist", mappedBy="events")
     */
    protected $artists;

    /**
     * Event constructor.
     */
    public function __construct()
    {
        $this->dateStart = new \DateTime();
        $this->artists = new ArrayCollection();
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
     * @return Event
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
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param mixed $hash
     * @return Event
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param mixed $dateStart
     * @return Event
     */
    public function setDateStart(\DateTime $dateStart)
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param mixed $dateEnd
     * @return Event
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     * @return Event
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getArtists()
    {
        return $this->artists;
    }

    /**
     * @param mixed $artists
     * @return Event
     */
    public function setArtists($artists)
    {
        $this->artists = $artists;
        return $this;
    }

    /**
     * @param Artist $artist
     * @return $this
     */
    public function addArtist(Artist $artist)
    {
        if (!$this->artists->contains($artist)) {
            $artist->addEvent($this);
            $this->artists->add($artist);
        }

        return $this;
    }

    /**
     * @param Artist $artist
     */
    public function removeArtist(Artist $artist)
    {
        if ($this->artists->contains($artist)) {
            $artist->removeEvent($this);
            $this->artists->remove($artist);
        }
    }
}