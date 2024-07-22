<?php

namespace FpDbTest\Placeholder;

interface ValueFormatterInterface
{
    public function format(mixed $value): string;
}
