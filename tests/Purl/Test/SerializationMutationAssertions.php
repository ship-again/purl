<?php

declare(strict_types=1);

namespace Purl\Test;

use Purl\AbstractPart;

trait SerializationMutationAssertions
{
    /**
     * @param array<int, array{expected: string, mutate: callable}> $steps
     */
    private function assertSerializationMutationSequence(
        AbstractPart $part,
        callable $serialize,
        string $initial,
        array $steps
    ): void {
        $this->assertSame($initial, \call_user_func($serialize, $part));

        foreach ($steps as $step) {
            \call_user_func($step['mutate'], $part);
            $this->assertSame($step['expected'], \call_user_func($serialize, $part));
        }
    }
}
