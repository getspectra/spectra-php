<?php

namespace Overtrue\Spectra\Expressions;

use Overtrue\Spectra\Operation;
use Overtrue\Spectra\Ref;
use Overtrue\Spectra\Utils;

class BinaryExpression implements ExpressionInterface
{
    public function __construct(public string $field, public string|Operation $operation, public mixed $value)
    {
        //
    }

    public function evaluate(array $data): bool
    {
        $operation = $this->operation instanceof Operation ? $this->operation : Operation::from($this->operation);

        if ($this->value instanceof Ref) {
            $rightValue = $this->value->toValue($data);
        } else {
            $rightValue = $this->value;
        }

        $leftValue = Utils::arrayGet($data, $this->field);

        return $operation->operate($leftValue, $rightValue);
    }

    public function getFields(): array
    {
        $fields = [$this->field];

        if ($this->value instanceof Ref) {
            $fields[] = $this->value->field;
        }

        return $fields;
    }
}