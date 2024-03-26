<?php

namespace Overtrue\Spectra\Polices;

use Overtrue\Spectra\Effect;
use Overtrue\Spectra\Expressions\ExpressionInterface;

interface PolicyInterface
{
    public function getDescription(): string;

    public function getApplyFilter(): ExpressionInterface;

    /**
     * @return array<string>
     */
    public function getFields(): array;

    /**
     * @return array<string>
     */
    public function getPermissions(): array;

    public function getEffect(): Effect;
}