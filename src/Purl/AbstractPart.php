<?php

declare(strict_types=1);

namespace Purl;

/**
 * AbstractPart class is implemented by each part of a Url where necessary.
 *
 * @implements \ArrayAccess<string, mixed>
 *
 * @psalm-api
 */
abstract class AbstractPart implements \ArrayAccess
{
    /** @var bool */
    protected $initialized = false;

    /** @var array<array-key, mixed> */
    protected $data = [];

    /** @var array<string, string> */
    protected $partClassMap = [];

    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * @param mixed $value
     */
    public function __set(string $key, $value): void
    {
        $this->set($key, $value);
    }

    public function __unset(string $key): void
    {
        $this->remove($key);
    }

    abstract public function __toString(): string;

    /**
     * @return array<array-key, mixed>
     */
    public function getData(): array
    {
        $this->initialize();

        return $this->data;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function setData(array $data): void
    {
        $this->initialize();

        $this->data = $data;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function has(string $key): bool
    {
        $this->initialize();

        return isset($this->data[$key]);
    }

    /**
     * @return null|mixed
     */
    public function get(string $key)
    {
        $this->initialize();

        return $this->data[$key] ?? null;
    }

    /**
     * @param mixed $value
     */
    public function set(string $key, $value): AbstractPart
    {
        $this->initialize();
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function add($value): AbstractPart
    {
        $this->initialize();
        $this->data[] = $value;

        return $this;
    }

    public function remove(string $key): AbstractPart
    {
        $this->initialize();

        unset($this->data[$key]);

        return $this;
    }

    /**
     * @param string $key
     */
    public function offsetExists($key): bool
    {
        $this->initialize();

        return isset($this->data[$key]);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * @param string $key
     */
    public function offsetUnset($key): void
    {
        $this->remove($key);
    }

    protected function initialize(): void
    {
        if (true === $this->initialized) {
            return;
        }

        $this->initialized = true;

        $this->doInitialize();
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function preparePartValue(string $key, $value)
    {
        if (!isset($this->partClassMap[$key])) {
            return $value;
        }

        $className = $this->partClassMap[$key];

        if (Path::class === $className) {
            return $value instanceof Path ? $value : new Path(is_string($value) ? $value : null);
        }

        if (Query::class === $className) {
            return $value instanceof Query ? $value : new Query(is_string($value) ? $value : null);
        }

        if (Fragment::class === $className) {
            if ($value instanceof Fragment) {
                return $value;
            }

            if ($value instanceof Path) {
                return new Fragment($value);
            }

            return new Fragment(is_string($value) ? $value : null);
        }

        return $value;
    }

    abstract protected function doInitialize(): void;
}
