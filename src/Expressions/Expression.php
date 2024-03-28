<?php

namespace Overtrue\Spectra\Expressions;

use Overtrue\Spectra\Utils;

abstract class Expression implements ExpressionInterface
{
    protected ?string $name = null;

    public function getName(): string
    {
        $name = $this->name ?? Utils::getBasename(static::class);

        if (str_ends_with($name, 'Expression')) {
            $name = substr($name, 0, -10);
        }

        return $name;
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize());
    }
}
