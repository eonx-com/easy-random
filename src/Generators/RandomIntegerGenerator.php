<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Generators;

use EonX\EasyRandom\Interfaces\RandomIntegerGeneratorInterface;

final class RandomIntegerGenerator implements RandomIntegerGeneratorInterface
{
    public function generate(?int $min = null, ?int $max = null): int
    {
        return \random_int($min ?? 0, $max ?? \PHP_INT_MAX);
    }
}
