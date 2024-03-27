<?php

namespace Overtrue\Spectra\Expressions;

interface ExpressionInterface extends \JsonSerializable, \Stringable
{
    public function evaluate(array $data): bool;

    /**
     * @return array<string>
     */
    public function getFields(): array;
}
