<?php

namespace FpDbTest\QueryTemplate;

use FpDbTest\Placeholder\SkipException;

final readonly class QueryTemplateProcessor {

    /**
     * @param \Closure(mixed $value, string $placeholderTag): string $placeholderCallback
     */
    public function __construct(private array $allowedPlaceholders, private \Closure $placeholderCallback)
    {

    }

    public function processQueryTemplate(string $query, array $values): string {
        $state = QueryProcessingState::NORMAL;
        $inCondition = false;
        $quoteSymbol = null;
        $resultQuery = '';
        $conditionQuery = '';
        $queryLength = strlen($query);
        $i = 0;

        /** ugly way to have reusable handler for placeholder building complete */
        $onPlaceholderBuildingComplete = function (string $placeholderType = null) use (&$i, &$inCondition, &$quoteSymbol, &$conditionQuery, &$resultQuery, &$query, &$values) {
            try {
                if (0 === count($values)) {
                    throw new InvalidTemplateSyntaxException('There is no value for condition', $query);
                }

                return ($this->placeholderCallback)(array_shift($values), $placeholderType);
            } catch (SkipException) {
                if (!$inCondition) {
                    throw new InvalidTemplateSyntaxException('Skip appeared outside of condition', $query);
                }
                $conditionQuery = '';
                $inCondition = false;
                while ('}' !== $query[$i]) {
                    $i++;
                }

                return '';
            }
        };

        for (; $i < $queryLength; $i++) {
            $iterResult = '';
            $char = $query[$i];

            switch ($state) {
                case QueryProcessingState::NORMAL:
                    if ('{' === $char) {
                        if ($inCondition) {
                            throw new InvalidTemplateSyntaxException('"{" is not allowed inside condition', $query);
                        }
                        $inCondition = true;
                        continue 2;
                    }
                    if ('}' === $char) {
                        if (!$inCondition) {
                            throw new InvalidTemplateSyntaxException('"}" is not allowed outside condition', $query);
                        }
                        $inCondition = false;
                        $resultQuery .= $conditionQuery;
                        $conditionQuery = '';
                        continue 2;
                    }

                    if ($char === '?') {
                        $state = QueryProcessingState::BUILD_PLACEHOLDER;
                        break;
                    }
                    if (in_array($char, ['"', "'"])) {
                        $state = QueryProcessingState::IN_QUOTES;
                        $quoteSymbol = $char;
                    }
                    $iterResult = $char;
                    break;

                case QueryProcessingState::IN_QUOTES:
                    if ($char === $quoteSymbol && '\\'!== $query[$i - 1]) {
                        $state = QueryProcessingState::NORMAL;
                    }
                    $iterResult = $char;
                    break;

                case QueryProcessingState::BUILD_PLACEHOLDER:
                    $placeholderType = null;
                    if (in_array($char, $this->allowedPlaceholders)) {
                        $placeholderType = $char;
                    } else {
                        --$i; // to re-read this character in normal state
                    }
                    $state = QueryProcessingState::NORMAL;
                    $iterResult = $onPlaceholderBuildingComplete($placeholderType);
                    break;
            }
            if ($inCondition) {
                $conditionQuery .= $iterResult;
            } else {
                $resultQuery .= $iterResult;
            }
        }

        if ($inCondition) {
            throw new InvalidTemplateSyntaxException('Non-closed condition', $query);
        }
        
        if (QueryProcessingState::BUILD_PLACEHOLDER === $state) {
            $resultQuery .= $onPlaceholderBuildingComplete(null);
        }
        
        return $resultQuery;
    }
}

enum QueryProcessingState
{
    case NORMAL;
    case IN_QUOTES;
    case BUILD_PLACEHOLDER;
}