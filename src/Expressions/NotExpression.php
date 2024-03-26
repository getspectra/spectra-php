<?php

namespace Overtrue\Spectra\Expressions;

class NotExpression implements ExpressionInterface
{
    public function __construct(public ExpressionInterface $expression)
    {
        //
    }

    public function evaluate(array $data): bool
    {
        return ! $this->expression->evaluate($data);
    }

    public function getFields(): array
    {
        return $this->expression->getFields();
    }
}