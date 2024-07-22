<?php

namespace FpDbTest\QueryTemplate;

final class InvalidTemplateSyntaxException extends \LogicException
{
    public function __construct(string $message, string $queryTemplate) {
        parent::__construct(sprintf('Syntax error in query: "%s". %s', $queryTemplate, $message));
    }
}