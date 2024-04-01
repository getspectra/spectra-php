<?php

namespace Overtrue\Spectra\Expressions;

use Overtrue\Spectra\Field;
use Overtrue\Spectra\Operation;
use Overtrue\Spectra\RefField;
use Overtrue\Spectra\Utils;

class Factory
{
    public static function make(string|Field $left, string|Operation $operation, int|float|bool|string|array|null|RefField $right): BinaryExpression
    {
        return new BinaryExpression($left, $operation, $right);
    }

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

    public static function eq(string|Field $left, int|float|bool|string|array|null|RefField $right): BinaryExpression
    {
        return new BinaryExpression($left, Operation::EQ, $right);
    }

    public static function ne(string|Field $left, int|float|bool|string|array|null|RefField $right): BinaryExpression
    {
        return new BinaryExpression($left, Operation::NEQ, $right);
    }

    public static function neq(string|Field $left, int|float|bool|string|array|null|RefField $right): BinaryExpression
    {
        return new BinaryExpression($left, Operation::NEQ, $right);
    }

    public static function gt(string|Field $left, int|float|bool|string|array|null|RefField $right): BinaryExpression
    {
        return new BinaryExpression($left, Operation::GT, $right);
    }

    public static function gte(string|Field $left, int|float|bool|string|array|null|RefField $right): BinaryExpression
    {
        return new BinaryExpression($left, Operation::GTE, $right);
    }

    public static function lt(string|Field $left, int|float|bool|string|array|null|RefField $right): BinaryExpression
    {
        return new BinaryExpression($left, Operation::LT, $right);
    }

    public static function lte(string|Field $left, int|float|bool|string|array|null|RefField $right): BinaryExpression
    {
        return new BinaryExpression($left, Operation::LTE, $right);
    }

    public static function in(string|Field $left, array|RefField $right): BinaryExpression
    {
        return new BinaryExpression($left, Operation::IN, $right);
    }

    public static function notIn(string|Field $left, array|RefField $right): BinaryExpression
    {
        return new BinaryExpression($left, Operation::NOT_IN, $right);
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
