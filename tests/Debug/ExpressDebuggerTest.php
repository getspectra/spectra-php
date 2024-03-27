<?php

namespace Overtrue\Spectra\Tests\Debug;

use Overtrue\Spectra\Debug\ExpressDebugger;
use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\Factory;
use Overtrue\Spectra\Ref;
use PHPUnit\Framework\TestCase;

class ExpressDebuggerTest extends TestCase
{
    public function testDebugWithBinaryExpression()
    {
        $expression = new BinaryExpression('user.id', '=', 1);
        $data = ['user.id' => 2];
        $report = ExpressDebugger::debug($expression, $data);

        $this->assertSame([
            'data' => $data,
            'expressions' => [
                'type' => 'BINARY',
                'result' => false,
                'expression' => [
                    'field' => 'user.id',
                    'operation' => '=',
                    'value' => 1,
                ],
            ]], $report);
    }

    public function testDebugWithBinaryExpressionWithRefValue()
    {
        $refValue = new Ref('team.creator_id');
        $expression = new BinaryExpression('user.id', '=', $refValue);
        $data = ['user.id' => 2, 'team.creator_id' => 1];
        $report = ExpressDebugger::debug($expression, $data);

        $this->assertSame([
            'data' => $data,
            'expressions' => [
                'type' => 'BINARY',
                'result' => false,
                'expression' => [
                    'field' => 'user.id',
                    'operation' => '=',
                    'value' => $refValue,
                ],
            ]], $report);
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
            'data' => $data,
            'expressions' => [
                'type' => 'AND',
                'result' => false,
                'expressions' => [
                    [
                        'type' => 'BINARY',
                        'result' => false,
                        'expression' => [
                            'field' => 'user.id',
                            'operation' => '=',
                            'value' => 1,
                        ],
                    ],
                    [
                        'type' => 'BINARY',
                        'result' => true,
                        'expression' => [
                            'field' => 'user.name',
                            'operation' => '=',
                            'value' => 'overtrue',
                        ],
                    ],
                ],
            ]], $report);
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
            'data' => $data,
            'expressions' => [
                'type' => 'OR',
                'result' => true,
                'expressions' => [
                    [
                        'type' => 'BINARY',
                        'result' => false,
                        'expression' => [
                            'field' => 'user.id',
                            'operation' => '=',
                            'value' => 1,
                        ],
                    ],
                    [
                        'type' => 'BINARY',
                        'result' => true,
                        'expression' => [
                            'field' => 'user.name',
                            'operation' => '=',
                            'value' => 'overtrue',
                        ],
                    ],
                ],
            ]], $report);
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
            'data' => $data,
            'expressions' => [
                'type' => 'NOT',
                'result' => true,
                'expression' => [
                    'type' => 'BINARY',
                    'result' => false,
                    'expression' => [
                        'field' => 'user.id',
                        'operation' => '=',
                        'value' => 1,
                    ],
                ],
            ],
        ], $report);
    }
}
