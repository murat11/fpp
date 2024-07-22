<?php

namespace FpDbTest\Placeholder\ValueFormaters;

use FpDbTest\Placeholder\ValueFormatterInterface;

final class NullFormatter implements ValueFormatterInterface
{

    public function format(mixed $value): string
    {
        assert(
            null === $value,
            new \InvalidArgumentException(
                sprintf('Only null values are allowed, "%s" given', gettype($value))
            )
        );

        return 'NULL';
    }
}
