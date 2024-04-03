<?php

namespace Overtrue\Spectra\Expressions;

use Overtrue\Spectra\Field;
use Overtrue\Spectra\Operation;
use Overtrue\Spectra\RefField;

class BinaryExpression extends Expression
{
    public Field $left;

    public function __construct(
        string $left,
        public string|Operation $operation,
        public int|float|bool|string|array|null|RefField $right
    ) {
        $this->left = new Field($left);
    }

    public function evaluate(array $data): bool
    {
        $operation = $this->operation instanceof Operation ? $this->operation : Operation::from(strtolower($this->operation));

        $rightValue = $this->right instanceof Field ? $this->right->evaluate($data) : $this->right;
        $leftValue = $this->left->evaluate($data);

        return $operation->operate($leftValue, $rightValue);
    }

    public function getFields(): array
    {
        $fields = [$this->left->name];

        if ($this->right instanceof Field) {
            $fields[] = $this->right->name;
        }

        return $fields;
    }

    public function jsonSerialize(): array
    {
        return [$this->left, $this->operation, $this->right];
    }
}
