<?php

namespace Overtrue\Spectra\Expressions;

use Overtrue\Spectra\Utils;

class NotExpression extends Expression
{
    public ExpressionInterface $expression;

    public function __construct(array|ExpressionInterface $expression)
    {
        $this->expression = Utils::normalizeExpression($expression);
    }

    public function evaluate(array $data): bool
    {
        return ! $this->expression->evaluate($data);
    }

    public function getFields(): array
    {
        return $this->expression->getFields();
    }

    public function jsonSerialize(): mixed
    {
        return ['not' => $this->expression];
    }
}
