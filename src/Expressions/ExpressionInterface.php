<?php

namespace Overtrue\Spectra\Expressions;

interface ExpressionInterface
{
    public function evaluate(array $data);

    /**
     * @return array<string>
     */
    public function getFields(): array;
}