<?php

namespace Overtrue\Spectra\Tests\Expressions;

use Overtrue\Spectra\Expressions\AndExpression;
use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\Factory;
use Overtrue\Spectra\Expressions\NotExpression;
use Overtrue\Spectra\Expressions\OrExpression;
use Overtrue\Spectra\Operation;
use Overtrue\Spectra\RefField;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testMake()
    {
        $expression = Factory::make('user.id', '=', 1);

        $this->assertInstanceOf(BinaryExpression::class, $expression);

        $this->assertSame('user.id', $expression->left->name);
        $this->assertSame('=', $expression->operation);
        $this->assertSame(1, $expression->right);
    }

    public function testAnd()
    {
        $expression = Factory::and([
            Factory::make('user.id', '=', 1),
            Factory::make('team.id', '=', 1),
        ]);

        $this->assertInstanceOf(AndExpression::class, $expression);

        $this->assertCount(2, $expression->expressions);

        $this->assertInstanceOf(BinaryExpression::class, $expression->expressions[0]);
        $this->assertInstanceOf(BinaryExpression::class, $expression->expressions[1]);

        $this->assertSame('user.id', $expression->expressions[0]->left->name);
        $this->assertSame('=', $expression->expressions[0]->operation);
        $this->assertSame(1, $expression->expressions[0]->right);

        $this->assertSame('team.id', $expression->expressions[1]->left->name);
        $this->assertSame('=', $expression->expressions[1]->operation);
        $this->assertSame(1, $expression->expressions[1]->right);
    }

    public function testOr()
    {
        $expression = Factory::or([
            Factory::make('user.id', '=', 1),
            Factory::make('team.id', '=', 1),
        ]);

        $this->assertInstanceOf(OrExpression::class, $expression);

        $this->assertCount(2, $expression->expressions);

        $this->assertInstanceOf(BinaryExpression::class, $expression->expressions[0]);
        $this->assertInstanceOf(BinaryExpression::class, $expression->expressions[1]);

        $this->assertSame('user.id', $expression->expressions[0]->left->name);
        $this->assertSame('=', $expression->expressions[0]->operation);
        $this->assertSame(1, $expression->expressions[0]->right);

        $this->assertSame('team.id', $expression->expressions[1]->left->name);
        $this->assertSame('=', $expression->expressions[1]->operation);
        $this->assertSame(1, $expression->expressions[1]->right);
    }

    public function testNot()
    {
        $expression = Factory::not(Factory::make('user.id', '=', 1));

        $this->assertInstanceOf(NotExpression::class, $expression);

        $this->assertInstanceOf(BinaryExpression::class, $expression->expression);

        $this->assertSame('user.id', $expression->expression->left->name);
        $this->assertSame('=', $expression->expression->operation);
        $this->assertSame(1, $expression->expression->right);
    }

    public function testOperations()
    {
        foreach (Operation::cases() as $operation) {
            $expression = forward_static_call(Factory::class.'::'.$operation->asMethodName(), 'user.id', [1]);

            $this->assertInstanceOf(BinaryExpression::class, $expression);

            $this->assertSame('user.id', $expression->left->name);
            $this->assertTrue($operation->equals($expression->operation));
            $this->assertSame([1], $expression->right);
        }
    }

    public function testParseBinaryExpression()
    {
        $expression = Factory::parse(json_encode(['user.id', '=', 1]));

        $this->assertInstanceOf(BinaryExpression::class, $expression);

        $this->assertSame('user.id', $expression->left->name);
        $this->assertSame('=', $expression->operation);
        $this->assertSame(1, $expression->right);
    }

    public function testParseNotExpression()
    {
        $expression = Factory::parse(json_encode([
            'not' => ['user.id', '=', 1],
        ]));

        $this->assertInstanceOf(NotExpression::class, $expression);
        $this->assertInstanceOf(BinaryExpression::class, $expression->expression);
        $this->assertSame('user.id', $expression->expression->left->name);
        $this->assertSame('=', $expression->expression->operation);

        // also support array
        $this->assertEquals($expression, Factory::parse([
            'not' => ['user.id', '=', 1],
        ]));
    }

    public function testParseAndExpression()
    {
        $expression = Factory::parse(json_encode([
            'and' => [
                ['user.id', '=', 1],
                ['team.id', '=', 1],
                ['file.team_id', '=', ['ref' => 'team.id']],
            ],
        ]));

        $this->assertInstanceOf(AndExpression::class, $expression);
        $this->assertCount(3, $expression->expressions);
        $this->assertInstanceOf(BinaryExpression::class, $expression->expressions[0]);
        $this->assertInstanceOf(BinaryExpression::class, $expression->expressions[1]);
        $this->assertSame('user.id', $expression->expressions[0]->left->name);
        $this->assertSame('=', $expression->expressions[0]->operation);
        $this->assertSame(1, $expression->expressions[0]->right);
        $this->assertSame('team.id', $expression->expressions[1]->left->name);
        $this->assertSame('=', $expression->expressions[1]->operation);
        $this->assertSame(1, $expression->expressions[1]->right);

        $this->assertInstanceOf(BinaryExpression::class, $expression->expressions[2]);
        $this->assertSame('file.team_id', $expression->expressions[2]->left->name);
        $this->assertSame('=', $expression->expressions[2]->operation);
        $this->assertInstanceOf(RefField::class, $expression->expressions[2]->right);
        $this->assertSame('team.id', $expression->expressions[2]->right->name);
    }

    public function testParseOrExpression()
    {
        $expression = Factory::parse(json_encode([
            'or' => [
                ['user.id', '=', 1],
                ['team.id', '=', 1],
            ],
        ]));

        $this->assertInstanceOf(OrExpression::class, $expression);
        $this->assertCount(2, $expression->expressions);
        $this->assertInstanceOf(BinaryExpression::class, $expression->expressions[0]);
        $this->assertInstanceOf(BinaryExpression::class, $expression->expressions[1]);
        $this->assertSame('user.id', $expression->expressions[0]->left->name);
        $this->assertSame('=', $expression->expressions[0]->operation);
        $this->assertSame(1, $expression->expressions[0]->right);
        $this->assertSame('team.id', $expression->expressions[1]->left->name);
        $this->assertSame('=', $expression->expressions[1]->operation);
        $this->assertSame(1, $expression->expressions[1]->right);
    }
}
