<?php

declare(strict_types=1);

namespace Purl\Benchmark;

use Purl\Url;

final class UrlBench
{
    private const URL = 'https://alice:secret@example.test:8443/catalog/search?q=purl&limit=25#tab?view=grid';

    private const TEXT = 'See https://example.test/catalog, https://example.test/search?q=purl and '
        .'http://legacy.example.test:8080/archive/item#details in this message.';

    public function benchParseAndBuild(): void
    {
        $url = new Url(self::URL);

        $url->getUrl();
    }

    public function benchMutateAndBuild(): void
    {
        $url = new Url(self::URL);

        $url->set('path', 'catalog/v2/items');
        $url->set('query', 'q=purl&limit=50');
        $url->set('fragment', 'details?view=list');
        $url->getUrl();
    }

    public function benchExtractFromText(): void
    {
        Url::extract(self::TEXT);
    }
}
