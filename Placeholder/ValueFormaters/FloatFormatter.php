<?php

namespace FpDbTest\Placeholder\ValueFormaters;

use FpDbTest\Placeholder\ValueFormatterInterface;

final class FloatFormatter implements ValueFormatterInterface
{
    public function format(mixed $value): string
    {
        assert(
            is_numeric($value),
            new \InvalidArgumentException(
                sprintf('Only numeric values are allowed, "%s" given', gettype($value))
            )
        );

        return (float) $value;
    }
}
