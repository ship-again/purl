<?php

declare(strict_types=1);

namespace Purl;

interface ParserInterface
{
    /**
     * @param null|string|Url $url
     *
     * @return array<string, null|int|string>
     */
    public function parseUrl($url): array;
}
