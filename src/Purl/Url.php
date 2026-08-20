<?php

declare(strict_types=1);

namespace Purl;

/**
 * Url is a simple OO class for manipulating Urls in PHP.
 *
 * @property null|string          $scheme
 * @property null|string          $host
 * @property null|int|string      $port
 * @property null|string          $user
 * @property null|string          $pass
 * @property null|Path|string     $path
 * @property null|Query|string    $query
 * @property null|Fragment|string $fragment
 * @property null|string          $canonical
 * @property null|string          $resource
 *
 * @psalm-api
 */
class Url extends AbstractPart
{
    /** @var array<array-key, mixed> */
    protected $data = [
        'scheme'             => null,
        'host'               => null,
        'port'               => null,
        'user'               => null,
        'pass'               => null,
        'path'               => null,
        'query'              => null,
        'fragment'           => null,
        'publicSuffix'       => null,
        'registerableDomain' => null,
        'subdomain'          => null,
        'canonical'          => null,
        'resource'           => null,
    ];

    /** @var array<string, string> */
    protected $partClassMap = [
        'path'     => 'Purl\Path',
        'query'    => 'Purl\Query',
        'fragment' => 'Purl\Fragment',
    ];

    /** @var null|string The original url string. */
    private $url;

    /** @var null|ParserInterface */
    private $parser;

    public function __construct(?string $url = null, ?ParserInterface $parser = null)
    {
        $this->url = $url;
        $this->parser = $parser;
    }

    public function __toString(): string
    {
        return $this->getUrl();
    }

    public static function parse(string $url): Url
    {
        return new self($url);
    }

    /**
     * @return Url[] $urls
     */
    public static function extract(string $string): array
    {
        $regex = '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\/\S*)?/';

        \preg_match_all($regex, $string, $matches);
        $urls = [];
        foreach ($matches[0] as $url) {
            $urls[] = self::parse($url);
        }

        return $urls;
    }

    public static function fromCurrent(): Url
    {
        $https = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : '';
        $serverPort = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 0;
        $hasHttps = isset($_SERVER['HTTPS']) && 'off' !== $https;
        $scheme = $hasHttps || 443 === $serverPort ? 'https' : 'http';

        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $baseUrl = \sprintf('%s://%s', $scheme, $host);

        $url = new self($baseUrl);

        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if ('' !== $requestUri) {
            if (false !== \strpos($requestUri, '?')) {
                $requestParts = \explode('?', $requestUri, 2);
                $path = $requestParts[0];
                $query = $requestParts[1] ?? '';
            } else {
                $path = $requestUri;
                $query = '';
            }

            $url->set('path', $path);
            $url->set('query', $query);
        }

        // Only set port if different from default (80 or 443)
        if ($serverPort > 0) {
            if (('http' === $scheme && 80 !== $serverPort)
                || ('https' === $scheme && 443 !== $serverPort)) {
                $url->set('port', $serverPort);
            }
        }

        // Authentication
        if (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER']) {
            $url->set('user', $_SERVER['PHP_AUTH_USER']);
            if (isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW']) {
                $url->set('pass', $_SERVER['PHP_AUTH_PW']);
            }
        }

        return $url;
    }

    public function getParser(): ParserInterface
    {
        if (null === $this->parser) {
            $this->parser = self::createDefaultParser();
        }

        return $this->parser;
    }

    public function setParser(ParserInterface $parser): void
    {
        $this->parser = $parser;
    }

    /**
     * @param string|Url $url
     */
    public function join($url): Url
    {
        $this->initialize();
        $parts = $this->getParser()->parseUrl($url);

        if (null !== $this->data['scheme']) {
            $parts['scheme'] = (string) $this->data['scheme'];
        }

        foreach ($parts as $key => $value) {
            if (null === $value) {
                continue;
            }

            $this->data[$key] = $value;
        }

        foreach (array_keys($this->data) as $key) {
            $this->data[$key] = $this->preparePartValue((string) $key, $this->data[$key]);
        }

        return $this;
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

    public function setFragment(Fragment $fragment): AbstractPart
    {
        $this->data['fragment'] = $fragment;

        return $this;
    }

    public function getFragment(): Fragment
    {
        $this->initialize();

        if (!$this->data['fragment'] instanceof Fragment) {
            $this->data['fragment'] = new Fragment(null);
        }

        return $this->data['fragment'];
    }

    public function getNetloc(): string
    {
        $this->initialize();

        $user = $this->user;
        $pass = $this->pass;
        $host = $this->host;
        $port = $this->port;

        $auth = null !== $user && null !== $pass ? $user.':'.$pass.'@' : '';

        return $auth.(string) $host.(null !== $port ? ':'.$port : '');
    }

    public function getUrl(): string
    {
        $this->initialize();

        $parts = \array_map(static function ($value): string {
            return (string) $value;
        }, $this->data);

        if (!$this->isAbsolute()) {
            return self::httpBuildRelativeUrl($parts);
        }

        return self::httpBuildUrl($parts);
    }

    public function setUrl(string $url): void
    {
        $this->initialized = false;
        $this->data = [];
        $this->url = $url;
    }

    public function isAbsolute(): bool
    {
        $this->initialize();

        return null !== $this->scheme && null !== $this->host;
    }

    protected function doInitialize(): void
    {
        $parts = $this->getParser()->parseUrl($this->url);

        foreach ($parts as $k => $v) {
            if (isset($this->data[$k])) {
                continue;
            }

            $this->data[$k] = $v;
        }

        foreach (array_keys($this->data) as $key) {
            $this->data[$key] = $this->preparePartValue((string) $key, $this->data[$key]);
        }
    }

    /**
     * @param string[] $parts
     */
    private static function httpBuildUrl(array $parts): string
    {
        $relative = self::httpBuildRelativeUrl($parts);

        $pass = '' !== $parts['pass'] ? \sprintf(':%s', $parts['pass']) : '';
        $auth = '' !== $parts['user'] ? \sprintf('%s%s@', $parts['user'], $pass) : '';
        $port = '' !== $parts['port'] ? \sprintf(':%d', $parts['port']) : '';

        return \sprintf(
            '%s://%s%s%s%s',
            $parts['scheme'],
            $auth,
            $parts['host'],
            $port,
            $relative
        );
    }

    /**
     * @param string[] $parts
     */
    private static function httpBuildRelativeUrl(array $parts): string
    {
        $parts['path'] = \ltrim($parts['path'], '/');

        return \sprintf(
            '/%s%s%s',
            $parts['path'],
            '' !== $parts['query'] ? '?'.$parts['query'] : '',
            '' !== $parts['fragment'] ? '#'.$parts['fragment'] : ''
        );
    }

    private static function createDefaultParser(): Parser
    {
        return new Parser();
    }
}
