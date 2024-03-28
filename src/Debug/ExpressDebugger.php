<?php

namespace Overtrue\Spectra\Debug;

use Overtrue\Spectra\Expressions\AndExpression;
use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\Expression;
use Overtrue\Spectra\Expressions\ExpressionInterface;
use Overtrue\Spectra\Expressions\NotExpression;
use Overtrue\Spectra\Expressions\OrExpression;
use Overtrue\Spectra\RefField;
use Overtrue\Spectra\Utils;

class ExpressDebugger
{
    public static function debug(ExpressionInterface $expression, array $data): array
    {
        if ($expression instanceof AndExpression || $expression instanceof OrExpression) {
            $expressions = [];

            foreach ($expression->expressions as $subExpression) {
                $expressions[] = forward_static_call(__METHOD__, $subExpression, $data);
            }

            return [
                'name' => self::getName($expression),
                'value' => $expression->evaluate($data),
                'expressions' => $expressions,
            ];
        }

        if ($expression instanceof NotExpression) {
            return [
                'name' => self::getName($expression),
                'value' => $expression->evaluate($data),
                'expression' => forward_static_call(__METHOD__, $expression->expression, $data),
            ];
        }

        if (! $expression instanceof BinaryExpression) {
            return [
                'name' => self::getName($expression),
                'value' => $expression->evaluate($data),
            ];
        }

        return [
            'name' => self::getName($expression),
            'value' => $expression->evaluate($data),
            'expression' => [
                'operation' => $expression->operation,
                'left' => [
                    'name' => $expression->left->name,
                    'value' => $expression->left->evaluate($data),
                ],
                'right' => $expression->right instanceof RefField
                    ? [
                        'name' => $expression->right->name,
                        'value' => $expression->right->evaluate($data),
                    ] : [
                        'name' => null,
                        'value' => $expression->right,
                    ],
            ],
        ];
    }

    public static function getName(ExpressionInterface $expression): string
    {
        if ($expression instanceof Expression) {
            return $expression->getName();
        }

        if (method_exists($expression, 'getName') && is_callable($expression, 'getName')) {
            return $expression->getName();
        }

        $name = get_class($expression);

        if (str_ends_with($name, 'Expression')) {
            $name = substr($name, 0, -10);
        }

        return Utils::getBasename($name);
    }
}
