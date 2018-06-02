<?php

namespace App\Model;

/**
 * Class ArtistModel
 * @package App\Model
 */
class ArtistModel
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var bool
     */
    protected $validated;

    /**
     * @var bool
     */
    protected $fromScrapper;

    /**
     * ArtistModel constructor.
     */
    public function __construct()
    {
        $this->fromScrapper = false;
        $this->validated = true;
    }

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
     * @return bool
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * @param bool $validated
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
    }

    /**
     * @return bool
     */
    public function isFromScrapper()
    {
        return $this->fromScrapper;
    }

    /**
     * @param bool $fromScrapper
     */
    public function setFromScrapper($fromScrapper)
    {
        $this->fromScrapper = $fromScrapper;
    }
}