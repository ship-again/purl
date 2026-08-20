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
    use SerializationMutationAssertions;

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
        $this->assertSerializationMutationSequence(
            new Path('initial'),
            function (Path $path): string {
                return $path->getPath();
            },
            'initial',
            [
                [
                    'expected' => 'changed',
                    'mutate'   => function (Path $path): void {
                        $path->setPath('changed');
                    },
                ],
                [
                    'expected' => 'set-data',
                    'mutate'   => function (Path $path): void {
                        $path->setData(['set-data']);
                    },
                ],
                [
                    'expected' => 'set-data/tail',
                    'mutate'   => function (Path $path): void {
                        $path->add('tail');
                    },
                ],
                [
                    'expected' => 'set-data',
                    'mutate'   => function (Path $path): void {
                        $path->remove('1');
                    },
                ],
                [
                    'expected' => 'set-data/array-access',
                    'mutate'   => function (Path $path): void {
                        $path['segment'] = 'array-access';
                    },
                ],
                [
                    'expected' => 'magic/unused',
                    'mutate'   => function (Path $path): void {
                        $path->setData(['magic']);
                        $path->value = 'unused';
                    },
                ],
            ]
        );
    }
}
