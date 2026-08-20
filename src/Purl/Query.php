<?php

declare(strict_types=1);

namespace Purl;

/**
 * Query represents the part of a Url after the question mark (?).
 *
 * @psalm-api
 */
class Query extends AbstractPart
{
    /** @var array<array-key, mixed> */
    protected $data = [];

    /** @var null|string The original query string. */
    private $query;

    public function __construct(?string $query = null)
    {
        $this->query = $query;
    }

    public function __toString(): string
    {
        return $this->getQuery();
    }

    public function getQuery(): string
    {
        $this->initialize();

        return \http_build_query($this->data);
    }

    public function setQuery(string $query): void
    {
        $this->initialized = false;
        $this->query = $query;
    }

    protected function doInitialize(): void
    {
        \parse_str((string) $this->query, $data);

        $this->data = $data;
    }
}
