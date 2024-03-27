<?php

namespace Overtrue\Spectra\Expressions;

use Overtrue\Spectra\Utils;

class AndExpression implements ExpressionInterface
{
    /** @var array<\Overtrue\Spectra\Expressions\ExpressionInterface> */
    public array $expressions;

    /**
     * @param  array<\Overtrue\Spectra\Expressions\ExpressionInterface>  $expressions
     */
    public function __construct(array $expressions)
    {
        $this->expressions = Utils::normalizeExpressions($expressions);
    }

    public function evaluate(array $data): bool
    {
        foreach ($this->expressions as $expression) {
            if (! $expression->evaluate($data)) {
                return false;
            }
        }

        return true;
    }

    public function getFields(): array
    {
        $fields = [];

        foreach ($this->expressions as $expression) {
            $fields = array_merge($fields, $expression->getFields());
        }

        return $fields;
    }

    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }

    public function jsonSerialize(): mixed
    {
        return ['and' => $this->expressions];
    }
}
