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
        $query = new Query('first=value');
        $this->assertSame('first=value', $query->getQuery());

        $query->setQuery('changed=value');
        $this->assertSame('changed=value', $query->getQuery());

        $query->setData(['set-data' => 'value']);
        $this->assertSame('set-data=value', $query->getQuery());

        $query->set('added', 'value');
        $this->assertSame('set-data=value&added=value', $query->getQuery());

        $query->remove('added');
        $this->assertSame('set-data=value', $query->getQuery());

        $query['array-access'] = 'value';
        $this->assertSame('set-data=value&array-access=value', $query->getQuery());

        $query->setData(['magic' => 'value']);
        $query->magic = 'changed';
        $this->assertSame('magic=changed', $query->getQuery());
    }
}
