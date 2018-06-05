<?php

namespace App\Model;

class LocationModel
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $address;

    /**
     * @var string|null
     */
    protected $postal_code;

    /**
     * @var string|null
     */
    protected $city;

    /**
     * @var string|null
     */
    protected $country;

    /**
     * @var float|null
     */
    protected $latitude;

    /**
     * @var float|null
     */
    protected $longitude;

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param null|string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return null|string
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param null|string $postal_code
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
    }

    /**
     * @return null|string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param null|string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return null|string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param null|string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return float|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float|null $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float|null $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }
}