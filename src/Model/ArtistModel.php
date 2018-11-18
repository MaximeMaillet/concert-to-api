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
    protected $exact;

    /**
     * ArtistModel constructor.
     */
    public function __construct()
    {
        $this->exact = true;
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
    public function isValidated(): bool
    {
        return $this->validated;
    }

    /**
     * @param bool $validated
     */
    public function setValidated(bool $validated)
    {
        $this->validated = $validated;
    }

    /**
     * @return bool
     */
    public function isExact(): bool
    {
        return $this->exact;
    }

    /**
     * @param bool $exact
     * @return $this
     */
    public function setExact(bool $exact)
    {
        $this->exact = $exact;

        return $this;
    }
}
