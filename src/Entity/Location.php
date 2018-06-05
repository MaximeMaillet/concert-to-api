<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Event;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 * @ORM\Table(name="locations")
 */
class Location
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Groups({"auth"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Groups({"auth"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"auth"})
     */
    protected $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"auth"})
     */
    protected $postal_code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Groups({"auth"})
     */
    protected $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"auth"})
     */
    protected $country;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotBlank()
     * @Groups({"auth"})
     */
    protected $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotBlank()
     * @Groups({"auth"})
     */
    protected $longitude;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="location")
     */
    protected $events;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"auth"})
     */
    protected $validated;

    /**
     * Location constructor.
     */
    public function __construct()
    {
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
     * @return Location
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
     * @return Location
     */
    public function setName(?string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     * @return Location
     */
    public function setAddress(?string $address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param mixed $postal_code
     * @return Location
     */
    public function setPostalCode(?string $postal_code)
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     * @return Location
     */
    public function setCity(?string $city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     * @return Location
     */
    public function setCountry(?string $country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     * @return Location
     */
    public function setLatitude(?float $latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     * @return Location
     */
    public function setLongitude(?float $longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isValidated() : bool
    {
        return $this->validated;
    }

    /**
     * @param bool $validated
     * @return Location
     */
    public function setValidated(bool $validated)
    {
        $this->validated = $validated;
        return $this;
    }

    /**
     * For ES
     */
    public function getLocation()
    {
        if (null !== $this->longitude && null !== $this->latitude) {
            return $this->latitude.','.$this->longitude;
        }

        return '0,0';
    }
}