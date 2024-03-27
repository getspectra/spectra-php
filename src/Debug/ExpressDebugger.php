<?php

namespace Overtrue\Spectra\Debug;

use Overtrue\Spectra\Expressions\AndExpression;
use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\ExpressionInterface;
use Overtrue\Spectra\Expressions\NotExpression;
use Overtrue\Spectra\Expressions\OrExpression;

class ExpressDebugger
{
    public static function debug(ExpressionInterface $expression, array $data): array
    {
        return [
            'data' => $data,
            'expressions' => self::debugExpress($expression, $data),
        ];
    }

    public static function debugExpress(ExpressionInterface $expression, array $data): array
    {
        if ($expression instanceof AndExpression || $expression instanceof OrExpression) {
            $expressions = [];

            foreach ($expression->expressions as $subExpression) {
                $expressions[] = forward_static_call(__METHOD__, $subExpression, $data);
            }

            return [
                'type' => $expression instanceof AndExpression ? 'AND' : 'OR',
                'result' => $expression->evaluate($data),
                'expressions' => $expressions,
            ];
        }

        if ($expression instanceof NotExpression) {
            return [
                'type' => 'NOT',
                'result' => $expression->evaluate($data),
                'expression' => forward_static_call(__METHOD__, $expression->expression, $data),
            ];
        }

        if (! $expression instanceof BinaryExpression) {
            return [
                'type' => 'CUSTOM',
                'result' => $expression->evaluate($data),
                'expression' => [
                    'class' => get_class($expression),
                ],
            ];
        }

        return [
            'type' => 'BINARY',
            'result' => $expression->evaluate($data),
            'expression' => [
                'field' => $expression->field,
                'operation' => $expression->operation,
                'value' => $expression->value,
            ],
        ];
    }
}
