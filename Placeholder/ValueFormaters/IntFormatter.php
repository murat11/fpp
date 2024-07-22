<?php

namespace FpDbTest\Placeholder\ValueFormaters;

use FpDbTest\Placeholder\ValueFormatterInterface;

final class IntFormatter implements ValueFormatterInterface
{

    public function format(mixed $value): string
    {
        assert(
            is_int($value) || is_bool($value) || is_float($value),
            new \InvalidArgumentException(
                sprintf('Only numeric values are allowed, "%s" given', gettype($value))
            )
        );

        return (int) $value;
    }
}
