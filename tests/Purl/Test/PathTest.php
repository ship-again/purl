<?php

declare(strict_types=1);

namespace Purl\Test;

use PHPUnit\Framework\TestCase;
use Purl\Path;

/**
 * @internal
 *
 * @coversNothing
 */
class PathTest extends TestCase
{
    public function testConstruct(): void
    {
        $path = new Path('test');
        $this->assertEquals('test', $path->getPath());
    }

    public function testGetSetPath(): void
    {
        $path = new Path();
        $this->assertEquals('', $path->getPath());
        $path->setPath('test');
        $this->assertEquals('test', $path->getPath());
    }

    public function testGetSegments(): void
    {
        $path = new Path('about/me');
        $this->assertEquals(['about', 'me'], $path->getSegments());
    }

    public function testToString(): void
    {
        $path = new Path('about/me');
        $this->assertEquals('about/me', (string) $path);
    }

    public function testSerializationCacheIsInvalidatedByPublicMutations(): void
    {
        $path = new Path('initial');
        $this->assertSame('initial', $path->getPath());

        $path->setPath('changed');
        $this->assertSame('changed', $path->getPath());

        $path->setData(['set-data']);
        $this->assertSame('set-data', $path->getPath());

        $path->add('tail');
        $this->assertSame('set-data/tail', $path->getPath());

        $path->remove('1');
        $this->assertSame('set-data', $path->getPath());

        $path['0'] = 'array-access';
        $this->assertSame('array-access', $path->getPath());

        $path->setData(['magic']);
        $path->value = 'unused';
        $this->assertSame('magic/unused', $path->getPath());
    }
}
