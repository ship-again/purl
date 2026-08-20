<?php

declare(strict_types=1);

namespace Purl;

/**
 * Fragment represents the part of a Url after the hashmark (#).
 *
 * @property Path|string  $path
 * @property Query|string $query
 *
 * @psalm-api
 */
class Fragment extends AbstractPart
{
    /** @var array<array-key, mixed> */
    protected $data = [
        'path'  => null,
        'query' => null,
    ];

    /** @var array<string, string> */
    protected $partClassMap = [
        'path'  => 'Purl\Path',
        'query' => 'Purl\Query',
    ];

    /** @var null|string The original fragment string. */
    private $fragment;

    /**
     * @param null|Path|string $fragment
     */
    public function __construct($fragment = null, ?Query $query = null)
    {
        if ($fragment instanceof Path) {
            $this->initialized = true;
            $this->data['path'] = $fragment;
        } else {
            $this->fragment = $fragment;
        }

        $this->data['query'] = $query;
    }

    public function __toString(): string
    {
        return $this->getFragment();
    }

    /**
     * @param mixed $value
     */
    public function set(string $key, $value): AbstractPart
    {
        $this->initialize();
        $this->data[$key] = $this->preparePartValue($key, $value);

        return $this;
    }

    public function getFragment(): string
    {
        $this->initialize();

        return \sprintf(
            '%s%s',
            (string) $this->path,
            '' !== (string) $this->query ? '?'.(string) $this->query : ''
        );
    }

    public function setFragment(string $fragment): AbstractPart
    {
        $this->initialized = false;
        $this->data = [];
        $this->fragment = $fragment;

        return $this;
    }

    public function setPath(Path $path): AbstractPart
    {
        $this->data['path'] = $path;

        return $this;
    }

    public function getPath(): Path
    {
        $this->initialize();

        if (!$this->data['path'] instanceof Path) {
            $this->data['path'] = new Path(null);
        }

        return $this->data['path'];
    }

    public function setQuery(Query $query): AbstractPart
    {
        $this->data['query'] = $query;

        return $this;
    }

    public function getQuery(): Query
    {
        $this->initialize();

        if (!$this->data['query'] instanceof Query) {
            $this->data['query'] = new Query(null);
        }

        return $this->data['query'];
    }

    protected function doInitialize(): void
    {
        if (null !== $this->fragment) {
            $parsed = \parse_url($this->fragment);

            if (\is_array($parsed)) {
                $this->data = \array_merge($this->data, $parsed);
            }
        }

        foreach (array_keys($this->data) as $key) {
            $this->data[$key] = $this->preparePartValue((string) $key, $this->data[$key]);
        }
    }
}
