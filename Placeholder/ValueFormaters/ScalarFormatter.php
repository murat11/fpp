<?php

namespace FpDbTest\Placeholder\ValueFormaters;

use FpDbTest\Placeholder\ValueFormatterInterface;

final readonly class ScalarFormatter implements ValueFormatterInterface
{
    public function __construct(
        private ValueFormatterInterface $intFormatter,
        private ValueFormatterInterface $floatFormatter,
        private ValueFormatterInterface $stringFormatter,
        private ValueFormatterInterface $boolFormatter,
    )
    {

    }

    public function format(mixed $value): string
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(
                sprintf('Value of type "%s" can not be replaced by %s', gettype($value), get_class($this))
            );
        }

        $formatter = match (true) {
            is_bool($value) => $this->boolFormatter,
            is_integer($value) => $this->intFormatter,
            is_float($value) => $this->floatFormatter,
            default => $this->stringFormatter,
        };

        return $formatter->format($value);
    }
}
