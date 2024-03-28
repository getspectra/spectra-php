<?php

namespace Overtrue\Spectra;

use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\ExpressionInterface;

class Utils
{
    public static function getBasename(string $name): string
    {
        return basename(str_replace('\\', '/', $name));
    }

    public static function isValidExpressTuple(array $definition): bool
    {
        return array_is_list($definition) && count($definition) === 3 && is_string($definition[0]) && Operation::tryFrom($definition[1]) !== null;
    }

    public static function arrayGet(array $array, string|int $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (! is_string($key) || ! str_contains($key, '.')) {
            return $default;
        }

        $items = $array;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($items) || ! array_key_exists($segment, $items)) {
                return $default;
            }

            $items = &$items[$segment];
        }

        return $items;
    }

    /**
     * @param  array<array<string, string, mixed>>|\Overtrue\Spectra\Expressions\ExpressionInterface  $expression
     */
    public static function normalizeExpression(array|ExpressionInterface $expression): ExpressionInterface
    {
        if ($expression instanceof ExpressionInterface) {
            return $expression;
        }

        if (! Utils::isValidExpressTuple($expression)) {
            throw new \InvalidArgumentException(sprintf('Invalid express definition: %s', json_encode($expression)));
        }

        return new BinaryExpression($expression[0], $expression[1], $expression[2]);
    }

    /**
     * @param array<ExpressionInterface|array<array<string,string,mixed>> $expressions
     * @return array<ExpressionInterface>
     */
    public static function normalizeExpressions(array $expressions): array
    {
        if (Utils::isValidExpressTuple($expressions)) {
            $expressions = [$expressions];
        }

        foreach ($expressions as $index => $expression) {
            if ($expression instanceof ExpressionInterface) {
                continue;
            }

            if (Utils::isValidExpressTuple($expression)) {
                $expressions[$index] = new BinaryExpression($expression[0], $expression[1], $expression[2]);

                continue;
            }

            throw new \InvalidArgumentException(sprintf('Invalid expression definition: %s', json_encode($expression)));
        }

        return $expressions;
    }

    public static function strIntend(string $string, int $size, string $char = ' '): string
    {
        return str_pad($string, strlen($string) + $size, $char, STR_PAD_LEFT);
    }

    public static function valueToString($value): string
    {
        return match (gettype($value)) {
            'string' => sprintf('"%s"', $value),
            'boolean' => $value ? 'true' : 'false',
            'integer' => (string) $value,
            'double' => (string) $value,
            'NULL' => 'null',
            default => json_encode($value),
        };
    }
}
