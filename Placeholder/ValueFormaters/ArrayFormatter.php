<?php

namespace FpDbTest\Placeholder\ValueFormaters;

use FpDbTest\Placeholder\ValueFormatterInterface;

final readonly class ArrayFormatter implements ValueFormatterInterface
{
    public function __construct(
        private ValueFormatterInterface $idFormatter,
        private ValueFormatterInterface $scalarFormatter,
    )
    {
    }

    public function format(mixed $value): string
    {
        assert(
            is_array($value),
            new \InvalidArgumentException(
                sprintf('Only array values are allowed for ArrayFormatter, "%s" given', gettype($value))
            )
        );

        return implode(
            ', ',
            array_map(
                function ($key, $val): string {
                    if (is_string($key)) {
                        return sprintf(
                            '%s = %s',
                            $this->idFormatter->format($key),
                            $this->scalarFormatter->format($val),
                        );
                    }

                    return $this->scalarFormatter->format($val);
                },
                array_keys($value),
                $value,
            )
        );
    }
}
