<?php

declare(strict_types=1);

namespace Purl\Benchmark;

use Purl\Path;
use Purl\Query;
use Purl\Url;

/**
 * Time-oriented subjects. Fixture construction belongs in a BeforeMethods
 * method whenever the subject is intended to measure a subsequent operation.
 */
final class UrlBench
{
    private const ABSOLUTE_URL = 'https://alice:secret@example.test:8443/catalog/search?q=purl&limit=25#tab?view=grid';
    private const RELATIVE_URL = '/catalog/search?q=purl&limit=25#tab?view=grid';
    private const JOIN_ABSOLUTE_URL = 'https://example.test/catalog/search?q=purl#results';
    private const JOIN_PROTOCOL_RELATIVE_URL = '//cdn.example.test/assets/app.js?version=2';
    private const SHORT_TEXT = 'See https://example.test/catalog in this message.';
    private const SPARSE_TEXT = 'See https://example.test/catalog in this message. ';
    private const DENSE_TEXT = 'https://a.example.test/one https://b.example.test/two '
        .'http://c.example.test/three ftp://d.example.test/four '
        .'ftps://e.example.test/five ';

    /** @var null|Url */
    private $initializedUrl;

    /** @var null|Url */
    private $mutatingUrl;

    /** @var null|Url */
    private $joinBaseUrl;

    /** @var null|Path */
    private $path;

    /** @var null|Query */
    private $query;

    /** @var string */
    private $sparseText;

    /** @var string */
    private $denseText;

    /** @Revs(1000) */
    public function benchParseAndBuildAbsolute(): void
    {
        $url = new Url(self::ABSOLUTE_URL);

        $url->getUrl();
    }

    /**
     * @Revs(1000)
     */
    public function benchParseAndBuildRelative(): void
    {
        $url = new Url(self::RELATIVE_URL);

        $url->getUrl();
    }

    /**
     * @BeforeMethods({"prepareInitializedUrl"})
     * @Revs(1000)
     */
    public function benchBuildInitialized(): void
    {
        $this->initializedUrl->getUrl();
    }

    /**
     * @BeforeMethods({"prepareMutatingUrl"})
     * @Revs(1000)
     */
    public function benchMutateAndBuild(): void
    {
        $this->mutatingUrl->set('path', 'catalog/v2/items');
        $this->mutatingUrl->set('query', 'q=purl&limit=50');
        $this->mutatingUrl->set('fragment', 'details?view=list');
        $this->mutatingUrl->getUrl();
    }

    /**
     * @BeforeMethods({"prepareJoinBaseUrl"})
     * @Revs(1000)
     */
    public function benchJoinAndBuild(): void
    {
        $this->joinBaseUrl->join(self::JOIN_ABSOLUTE_URL)->getUrl();
    }

    /**
     * @BeforeMethods({"prepareJoinBaseUrl"})
     * @Revs(1000)
     */
    public function benchJoinProtocolRelativeAndBuild(): void
    {
        $this->joinBaseUrl->join(self::JOIN_PROTOCOL_RELATIVE_URL)->getUrl();
    }

    /**
     * @Revs(1000)
     */
    public function benchParseAndReadScalar(): void
    {
        $url = new Url(self::ABSOLUTE_URL);

        (string) $url->host;
    }

    /**
     * @Revs(1000)
     */
    public function benchExtractShort(): void
    {
        Url::extract(self::SHORT_TEXT);
    }

    /**
     * @BeforeMethods({"prepareExtractTexts"})
     * @Revs(1000)
     */
    public function benchExtractBulkSparse(): void
    {
        Url::extract($this->sparseText);
    }

    /**
     * @BeforeMethods({"prepareExtractTexts"})
     * @Revs(1000)
     */
    public function benchExtractBulkDense(): void
    {
        Url::extract($this->denseText);
    }

    /**
     * @BeforeMethods({"prepareParts"})
     * @Revs(1000)
     */
    public function benchPathBuild(): void
    {
        (string) $this->path;
    }

    /**
     * @BeforeMethods({"prepareParts"})
     * @Revs(1000)
     */
    public function benchQueryBuild(): void
    {
        (string) $this->query;
    }

    /**
     * @BeforeMethods({"prepareParts"})
     * @Revs(1000)
     */
    public function benchRepeatedPartBuild(): void
    {
        (string) $this->path;
        (string) $this->query;
        (string) $this->path;
        (string) $this->query;
    }

    public function prepareInitializedUrl(): void
    {
        $this->initializedUrl = new Url(self::ABSOLUTE_URL);
        $this->initializedUrl->getUrl();
    }

    public function prepareMutatingUrl(): void
    {
        $this->mutatingUrl = new Url(self::ABSOLUTE_URL);
    }

    public function prepareJoinBaseUrl(): void
    {
        $this->joinBaseUrl = new Url('https://base.example.test/base?keep=yes#old');
    }

    public function prepareExtractTexts(): void
    {
        $this->sparseText = \str_repeat(self::SPARSE_TEXT, 200);
        $this->denseText = \str_repeat(self::DENSE_TEXT, 200);
    }

    public function prepareParts(): void
    {
        $this->path = new Path('catalog/search/with spaces');
        $this->query = new Query('q=purl&limit=25&sort=desc');
        $this->path->getPath();
        $this->query->getQuery();
    }
}
