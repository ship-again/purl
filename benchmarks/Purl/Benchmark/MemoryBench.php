<?php

declare(strict_types=1);

namespace Purl\Benchmark;

use Purl\Url;

/**
 * Memory-only subjects intentionally retain their batch on the benchmark
 * object. This makes mem.final and mem.peak describe the live batch rather
 * than a temporary return value which can be collected before measurement.
 *
 * @Executor("memory_centric_microtime")
 */
final class MemoryBench
{
    private const BATCH_SIZE = 10000;

    /** @var Url[] */
    private $batch = [];

    /** @var string[] */
    private $inputs = [];

    /**
     * @Revs(1)
     * @BeforeMethods({"prepareInputs"})
     */
    public function benchBatchParseAndBuild(): void
    {
        $this->batch = [];

        foreach ($this->inputs as $input) {
            $url = new Url($input);
            $url->getUrl();
            $this->batch[] = $url;
        }
    }

    /**
     * @Revs(1)
     * @BeforeMethods({"prepareInputs"})
     */
    public function benchBatchParseAndReadScalar(): void
    {
        $this->batch = [];

        foreach ($this->inputs as $input) {
            $url = new Url($input);
            (string) $url->host;
            $this->batch[] = $url;
        }
    }

    /**
     * @Revs(1)
     * @BeforeMethods({"prepareInputs"})
     */
    public function benchBatchSerializeParts(): void
    {
        $this->batch = [];

        foreach ($this->inputs as $input) {
            $url = new Url($input);
            $url->getPath()->getPath();
            $url->getQuery()->getQuery();
            $url->getFragment()->getFragment();
            $this->batch[] = $url;
        }
    }

    public function prepareInputs(): void
    {
        $this->inputs = [];

        for ($index = 0; $index < self::BATCH_SIZE; ++$index) {
            $this->inputs[] = 'https://user:pass@example'.$index.'.test/catalog/item-'.$index
                .'?q=purl&limit='.$index.'#tab?view=grid';
        }
    }
}
