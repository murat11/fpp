<?php

namespace FpDbTest\Placeholder\ValueFormaters;

use FpDbTest\Placeholder\ValueFormatterInterface;

final class StringFormatter implements ValueFormatterInterface
{

    public function format(mixed $value): string
    {
        assert(
            is_scalar($value),
            new \InvalidArgumentException(
                sprintf('Only scalar values are allowed for StringFormatter, "%s" given', gettype($value))
            )
        );

        return sprintf("'%s'", str_replace("'", "\'", $value, ));
    }
}
