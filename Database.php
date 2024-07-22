<?php

namespace FpDbTest;

use FpDbTest\Placeholder\PlaceholderReplacer;
use FpDbTest\Placeholder\Skip;
use FpDbTest\QueryTemplate\QueryTemplateProcessor;

final readonly class Database implements DatabaseInterface
{
//    private mysqli $mysqli;

    private PlaceholderReplacer $placeholderReplacer;

    public function __construct()
    {
//        $this->mysqli = $mysqli;
        $this->placeholderReplacer = PlaceholderReplacer::create();
    }

    public function buildQuery(string $query, array $args = []): string
    {
        $queryBuilderAssistant = new QueryTemplateProcessor(
            allowedPlaceholders: PlaceholderReplacer::ALLOWED_TAGS,
            placeholderCallback: function (mixed $value, string $placeholderTag = null): string {
                return $this->placeholderReplacer->format($value, $placeholderTag);
            }
        );

        return $queryBuilderAssistant->processQueryTemplate($query, $args);
    }

    public function skip()
    {
        return new Skip();
    }
}
