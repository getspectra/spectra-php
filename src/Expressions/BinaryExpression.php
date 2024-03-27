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
        $operation = $this->operation instanceof Operation ? $this->operation : Operation::from(strtolower($this->operation));

        $rightValue = $this->getRightValue($this->value, $data);

        $leftValue = Utils::arrayGet($data, $this->field);

        return $operation->operate($leftValue, $rightValue);
    }

    protected function getRightValue(mixed $value, array $data): mixed
    {
        return $value instanceof Ref ? $value->toValue($data) : $value;
    }

    public function getFields(): array
    {
        $fields = [$this->field];

        if ($this->value instanceof Ref) {
            $fields[] = $this->value->field;
        }

        return $fields;
    }

    public function jsonSerialize(): array
    {
        return [$this->field, $this->operation, $this->value];
    }

    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }
}
