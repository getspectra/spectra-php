<?php

namespace Overtrue\Spectra\Tests\Debug;

use Overtrue\Spectra\Debug\ExpressDebugger;
use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\Factory;
use Overtrue\Spectra\RefField;
use PHPUnit\Framework\TestCase;

class ExpressDebuggerTest extends TestCase
{
    public function testDebugWithBinaryExpression()
    {
        $expression = new BinaryExpression('user.id', '=', 1);
        $data = ['user.id' => 2];
        $report = ExpressDebugger::debug($expression, $data);

        $this->assertSame([
            'name' => 'Binary',
            'value' => false,
            'expression' => [
                'operation' => '=',
                'left' => ['name' => 'user.id', 'value' => 2],
                'right' => ['name' => null, 'value' => 1],
            ],
        ], $report);
    }

    public function testDebugWithBinaryExpressionWithRefValue()
    {
        $refValue = new RefField('team.creator_id');
        $expression = new BinaryExpression('user.id', '=', $refValue);
        $data = ['user.id' => 2, 'team.creator_id' => 1];
        $report = ExpressDebugger::debug($expression, $data);

        $this->assertSame([
            'name' => 'Binary',
            'value' => false,
            'expression' => [
                'operation' => '=',
                'left' => ['name' => 'user.id', 'value' => 2],
                'right' => ['name' => 'team.creator_id', 'value' => 1],
            ],
        ], $report);
    }

    public function testDebugWithAndExpression()
    {
        $expression = Factory::and([
            new BinaryExpression('user.id', '=', 1),
            new BinaryExpression('user.name', '=', 'overtrue'),
        ]);

        $data = ['user.id' => 2, 'user.name' => 'overtrue'];
        $report = ExpressDebugger::debug($expression, $data);

        $this->assertSame([
            'name' => 'And',
            'value' => false,
            'expressions' => [
                [
                    'name' => 'Binary',
                    'value' => false,
                    'expression' => [
                        'operation' => '=',
                        'left' => ['name' => 'user.id', 'value' => 2],
                        'right' => ['name' => null, 'value' => 1],
                    ],
                ],
                [
                    'name' => 'Binary',
                    'value' => true,
                    'expression' => [
                        'operation' => '=',
                        'left' => ['name' => 'user.name', 'value' => 'overtrue'],
                        'right' => ['name' => null, 'value' => 'overtrue'],
                    ],
                ],
            ],
        ], $report);
    }

    public function testDebugWithOrExpression()
    {
        $expression = Factory::or([
            new BinaryExpression('user.id', '=', 1),
            new BinaryExpression('user.name', '=', 'overtrue'),
        ]);
        $data = ['user.id' => 2, 'user.name' => 'overtrue'];
        $report = ExpressDebugger::debug($expression, $data);

        $this->assertSame([
            'name' => 'Or',
            'value' => true,
            'expressions' => [
                [
                    'name' => 'Binary',
                    'value' => false,
                    'expression' => [
                        'operation' => '=',
                        'left' => ['name' => 'user.id', 'value' => 2],
                        'right' => ['name' => null, 'value' => 1],
                    ],
                ],
                [
                    'name' => 'Binary',
                    'value' => true,
                    'expression' => [
                        'operation' => '=',
                        'left' => ['name' => 'user.name', 'value' => 'overtrue'],
                        'right' => ['name' => null, 'value' => 'overtrue'],
                    ],
                ],
            ],
        ], $report);
    }

    public function testDebugWithNotExpression()
    {
        $expression = Factory::not(new BinaryExpression('user.id',
            '=',
            1
        ));

        $data = ['user.id' => 2];
        $report = ExpressDebugger::debug($expression, $data);

        $this->assertSame([
            'name' => 'Not',
            'value' => true,
            'expression' => [
                'name' => 'Binary',
                'value' => false,
                'expression' => [
                    'operation' => '=',
                    'left' => ['name' => 'user.id', 'value' => 2],
                    'right' => ['name' => null, 'value' => 1],
                ],
            ],
        ], $report);
    }
}
