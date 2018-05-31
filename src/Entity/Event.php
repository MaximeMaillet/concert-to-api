<?php

namespace App\Entity;

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
     * Event constructor.
     */
    public function __construct()
    {
        $this->dateStart = new \DateTime();
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
}