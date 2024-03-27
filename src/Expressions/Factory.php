<?php

namespace Overtrue\Spectra\Expressions;

use Overtrue\Spectra\Utils;

class Factory
{
    /**
     * @param array<ExpressionInterface|array<array<string,string,mixed>> $expressions
     */
    public static function and(array $expressions): AndExpression
    {
        return new AndExpression($expressions);
    }

    /**
     * @param array<ExpressionInterface|array<array<string,string,mixed>> $expressions
     */
    public static function or(array $expressions): OrExpression
    {
        return new OrExpression($expressions);
    }

    public static function not(array|ExpressionInterface $expression): NotExpression
    {
        return new NotExpression($expression);
    }

    public static function parse(array|string $definition): ExpressionInterface
    {
        if (is_string($definition)) {
            $definition = json_decode($definition, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid expression definition');
            }
        }

        // ['field', '=', 'value']
        if (array_is_list($definition)) {
            return Utils::normalizeExpression($definition);
        }

        // {'and': [['field1', '=', 'value'], ['field2', '=', 'value'], ...]}
        if (array_key_exists('and', $definition)) {
            return new AndExpression(array_map(__METHOD__, $definition['and']));
        }

        // {'or': [['field1', '=', 'value'], ['field2', '=', 'value'], ...]}
        if (array_key_exists('or', $definition)) {
            return new OrExpression(array_map(__METHOD__, $definition['or']));
        }

        // {'not': ['field1', '=', 'value']}
        if (array_key_exists('not', $definition)) {
            return new NotExpression(self::parse($definition['not']));
        }

        throw new \InvalidArgumentException('Invalid expression definition');
    }
}
