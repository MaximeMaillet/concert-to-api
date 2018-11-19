<?php

namespace App\Model;

/**
 * Class ArtistModel
 * @package App\Model
 */
class ArtistModel
{
    public const DEFAULT_LIMIT = 20;
    public const DEFAULT_PAGE = 1;

    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $limit;

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
        $this->exact = false;
        $this->validated = true;
        $this->limit = self::DEFAULT_LIMIT;
        $this->page = self::DEFAULT_PAGE;
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

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return ArtistModel
     */
    public function setPage(int $page): ArtistModel
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return ArtistModel
     */
    public function setLimit(int $limit): ArtistModel
    {
        $this->limit = $limit;
        return $this;
    }
}
