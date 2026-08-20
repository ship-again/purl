<?php

declare(strict_types=1);

namespace Purl;

/**
 * Path represents the part of a Url after the domain suffix and before the hashmark (#).
 *
 * @psalm-api
 */
class Path extends AbstractPart
{
    /** @var array<array-key, mixed> */
    protected $data = [];

    /** @var null|string The original path string. */
    private $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path;
    }

    public function __toString(): string
    {
        return $this->getPath();
    }

    public function getPath(): string
    {
        $this->initialize();

        /** @var string[] $segments */
        $segments = $this->data;

        return \implode('/', \array_map(static function (string $value): string {
            return \str_replace(' ', '%20', $value);
        }, $segments));
    }

    public function setPath(string $path): void
    {
        $this->initialized = false;
        $this->path = $path;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getSegments(): array
    {
        $this->initialize();

        return $this->data;
    }

    protected function doInitialize(): void
    {
        $this->data = \explode('/', (string) $this->path);
    }
}
