<?php

namespace Overtrue\Spectra\Expressions;

class AndExpression implements ExpressionInterface
{
    /**
     * @param  array<\Overtrue\Spectra\Expressions\ExpressionInterface>  $expressions
     */
    public function __construct(public array $expressions)
    {

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
}
