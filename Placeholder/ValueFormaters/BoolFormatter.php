<?php

namespace FpDbTest\Placeholder\ValueFormaters;

use FpDbTest\Placeholder\ValueFormatterInterface;

final class BoolFormatter implements ValueFormatterInterface
{

    public function format(mixed $value): string
    {
        assert(
            is_bool($value),
            new \InvalidArgumentException(
                sprintf('Only integer values are allowed, "%s" given', gettype($value))
            )
        );

        return $value ? 1 : 0;
    }
}
