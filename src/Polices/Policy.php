<?php

namespace Overtrue\Spectra\Polices;

use Overtrue\Spectra\Effect;
use Overtrue\Spectra\Expressions\ExpressionInterface;
use Overtrue\Spectra\Utils;

class Policy implements PolicyInterface
{
    public function __construct(
        public ExpressionInterface $expression,
        public Effect $effect = Effect::ALLOW,
        public array $permissions = [],
        public string $description = '',
    ) {
        //
    }

    public function getDescription(): string
    {
        return $this->description ?? Utils::getClassBasename(get_class($this));
    }

    public function getFields(): array
    {
        return $this->getApplyFilter()->getFields();
    }

    public function getPermissions(): array
    {
        if (property_exists($this, 'permissions')) {
            return $this->permissions;
        }

        return [];
    }

    public function getEffect(): Effect
    {
        return $this->effect ?? Effect::ALLOW;
    }

    public function getApplyFilter(): ExpressionInterface
    {
        return $this->expression;
    }
}