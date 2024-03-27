<?php

namespace Overtrue\Spectra\Expressions;

class OrExpression implements ExpressionInterface
{
    public function __construct(public array $expressions)
    {
        //
    }

    public function evaluate(array $data): bool
    {
        foreach ($this->expressions as $expression) {
            if ($expression->evaluate($data)) {
                return true;
            }
        }

        return false;
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
        return ['or' => $this->expressions];
    }
}
