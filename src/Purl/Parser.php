<?php

declare(strict_types=1);

namespace Purl;

/**
 * Parser class.
 *
 * @psalm-api
 */
class Parser implements ParserInterface
{
    /** @var array<string, null|int|string> */
    private static $defaultParts = [
        'scheme'    => null,
        'host'      => null,
        'port'      => null,
        'user'      => null,
        'pass'      => null,
        'path'      => null,
        'query'     => null,
        'fragment'  => null,
        'canonical' => null,
        'resource'  => null,
    ];

    /**
     * @param null|string|Url $url
     *
     * @return array<string, null|int|string>
     */
    public function parseUrl($url): array
    {
        $url = (string) $url;

        $parsedUrl = $this->doParseUrl($url);

        if ([] === $parsedUrl) {
            throw new \InvalidArgumentException(\sprintf('Invalid url %s', $url));
        }

        $parsedUrl = \array_merge(self::$defaultParts, $parsedUrl);

        if (isset($parsedUrl['host'])) {
            $host = (string) $parsedUrl['host'];
            $path = isset($parsedUrl['path']) ? (string) $parsedUrl['path'] : '';
            $query = isset($parsedUrl['query']) ? (string) $parsedUrl['query'] : null;

            $parsedUrl['canonical'] = \implode('.', \array_reverse(\explode('.', $host))).$path.(null !== $query ? '?'.$query : '');

            $parsedUrl['resource'] = $path;

            if (null !== $query) {
                $parsedUrl['resource'] .= '?'.$query;
            }
        }

        return $parsedUrl;
    }

    /**
     * @return array<string, int|string>
     */
    protected function doParseUrl(string $url): array
    {
        $parsedUrl = \parse_url($url);

        return false !== $parsedUrl ? $parsedUrl : [];
    }
}
