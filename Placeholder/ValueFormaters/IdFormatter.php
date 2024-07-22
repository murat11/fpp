<?php

namespace FpDbTest\Placeholder\ValueFormaters;

use FpDbTest\Placeholder\ValueFormatterInterface;

final class IdFormatter implements ValueFormatterInterface
{

    public function format(mixed $value): string
    {
        assert(
            is_array($value) || is_string($value),
            new \InvalidArgumentException(
                sprintf(
                    'Only array or string values are allowed for IdFormatter, "%s" given',
                    gettype($value)
                )
            )
        );

        if (is_array($value)) {
            return implode(
                ', ',
                array_map(
                    function ($val):string {
                        assert(
                            is_string($val),
                            new \InvalidArgumentException(
                                sprintf(
                                    'Only string values are allowed as elements for array passed to IdFormatter, "%s" given',
                                    gettype($val)
                                )
                            )
                        );

                        return $this->format($val);
                    },
                    $value
                )
            );
        }

        if (str_contains($value, '`')) {
            throw new \InvalidArgumentException(sprintf('"`" sign detected in replacement for SQL id in "%s"', $value));
        }

        return sprintf('`%s`', $value);
    }
}
