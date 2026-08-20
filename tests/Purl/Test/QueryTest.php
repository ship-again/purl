<?php

declare(strict_types=1);

namespace Purl\Test;

use PHPUnit\Framework\TestCase;
use Purl\Query;

/**
 * @internal
 *
 * @coversNothing
 */
class QueryTest extends TestCase
{
    use SerializationMutationAssertions;

    public function testConstruct(): void
    {
        $query = new Query('param=value');
        $this->assertEquals('param=value', $query->getQuery());
    }

    public function testGetSetQuery(): void
    {
        $query = new Query();
        $this->assertEquals('', $query->getQuery());
        $query->setQuery('param1=value1&param2=value2');
        $this->assertEquals('param1=value1&param2=value2', $query->getQuery());
    }

    public function testToString(): void
    {
        $query = new Query('param1=value1&param2=value2');
        $this->assertEquals('param1=value1&param2=value2', (string) $query);
    }

    public function testGetSetData(): void
    {
        $query = new Query('param1=value1&param2=value2');
        $this->assertEquals(['param1' => 'value1', 'param2' => 'value2'], $query->getData());
        $query->setData(['param' => 'value']);
        $this->assertEquals('param=value', $query->getQuery());
    }

    public function testSerializationCacheIsInvalidatedByPublicMutations(): void
    {
        $this->assertSerializationMutationSequence(
            new Query('first=value'),
            function (Query $query): string {
                return $query->getQuery();
            },
            'first=value',
            [
                [
                    'expected' => 'changed=value',
                    'mutate'   => function (Query $query): void {
                        $query->setQuery('changed=value');
                    },
                ],
                [
                    'expected' => 'set-data=value',
                    'mutate'   => function (Query $query): void {
                        $query->setData(['set-data' => 'value']);
                    },
                ],
                [
                    'expected' => 'set-data=value&added=value',
                    'mutate'   => function (Query $query): void {
                        $query->set('added', 'value');
                    },
                ],
                [
                    'expected' => 'set-data=value',
                    'mutate'   => function (Query $query): void {
                        $query->remove('added');
                    },
                ],
                [
                    'expected' => 'set-data=value&array-access=value',
                    'mutate'   => function (Query $query): void {
                        $query['array-access'] = 'value';
                    },
                ],
                [
                    'expected' => 'magic=changed',
                    'mutate'   => function (Query $query): void {
                        $query->setData(['magic' => 'value']);
                        $query->magic = 'changed';
                    },
                ],
            ]
        );
    }
}
