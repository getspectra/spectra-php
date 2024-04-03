<?php

namespace Overtrue\Spectra\Polices;

use Overtrue\Spectra\Effect;
use Overtrue\Spectra\Expressions\ExpressionInterface;

interface PolicyInterface
{
    public function getDescription(): string;

    public function getFilter(): ExpressionInterface;

    /**
     * @return array<string>
     */
    public function getFields(): array;

    /**
     * @return array<string>
     */
    public function getPermissions(): array;

    public function getEffect(): Effect;

    /**
     * Return the data matched by the policy.
     */
    public function apply(array $data): bool;
}
