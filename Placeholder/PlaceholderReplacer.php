<?php
namespace FpDbTest\Placeholder;

use FpDbTest\Placeholder\ValueFormaters\ArrayFormatter;
use FpDbTest\Placeholder\ValueFormaters\BoolFormatter;
use FpDbTest\Placeholder\ValueFormaters\FloatFormatter;
use FpDbTest\Placeholder\ValueFormaters\IdFormatter;
use FpDbTest\Placeholder\ValueFormaters\IntFormatter;
use FpDbTest\Placeholder\ValueFormaters\NullFormatter;
use FpDbTest\Placeholder\ValueFormaters\ScalarFormatter;
use FpDbTest\Placeholder\ValueFormaters\StringFormatter;

final class PlaceholderReplacer
{
    public const string TAG_INT = 'd';
    public const string TAG_FLOAT = 'f';
    public const string TAG_ARRAY = 'a';
    public const string TAG_ID = '#';


    public static function create(): self
    {
        $intFormatter = new IntFormatter();
        $floatFormatter = new FloatFormatter();
        $idFormatter = new IdFormatter();
        $scalarFormatter = new ScalarFormatter(
            $intFormatter,
            $floatFormatter,
            new StringFormatter(),
            new BoolFormatter(),
        );
        $arrayFormatter = new ArrayFormatter(
            $idFormatter,
            self::getWrapped($scalarFormatter),
        );
        return new self(
            self::getWrapped($intFormatter),
            self::getWrapped($floatFormatter),
            self::getWrapped($scalarFormatter),
            self::getWrapped($arrayFormatter),
            $idFormatter,
        );
    }


    private function __construct(
        private readonly ValueFormatterInterface $intFormatter,
        private readonly ValueFormatterInterface $floatFormatter,
        private readonly ValueFormatterInterface $scalarFormatter,
        private readonly ValueFormatterInterface $arrayFormatter,
        private readonly ValueFormatterInterface $idFormatter,
    )
    {

    }

    public const array ALLOWED_TAGS = [
        self::TAG_INT,
        self::TAG_FLOAT,
        self::TAG_ARRAY,
        self::TAG_ID,
    ];

    public function format(mixed $value, string $placeholderTag = null): string
    {
        return $this->getFormatterByTag($placeholderTag)->format($value);
    }

    private static function getWrapped(ValueFormatterInterface $formatter): ValueFormatterInterface
    {
        return (new class($formatter) implements ValueFormatterInterface {
            public function __construct(private readonly ValueFormatterInterface $formater)
            {
            }

            public function format(mixed $value): string
            {
                if (null === $value) {
                    return (new NullFormatter())->format($value);
                }

                if ($value instanceof Skip) {
                    throw new SkipException();
                }

                return $this->formater->format($value);
            }
        });
    }

    private function getFormatterByTag(?string $placeholderTag): ValueFormatterInterface
    {
        return match ($placeholderTag) {
            null => $this->scalarFormatter,
            self::TAG_INT => $this->intFormatter,
            self::TAG_FLOAT => $this->floatFormatter,
            self::TAG_ARRAY => $this->arrayFormatter,
            self::TAG_ID => $this->idFormatter,
        };
    }
}
