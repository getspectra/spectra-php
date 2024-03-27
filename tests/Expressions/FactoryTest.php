<?php

namespace Overtrue\Spectra\Tests\Expressions;

use Overtrue\Spectra\Expressions\AndExpression;
use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\Factory;
use Overtrue\Spectra\Expressions\NotExpression;
use Overtrue\Spectra\Expressions\OrExpression;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testParseBinaryExpression()
    {
        $expression = Factory::parse(json_encode(['user.id', '=', 1]));

        $this->assertInstanceOf(BinaryExpression::class, $expression);

        $this->assertSame('user.id', $expression->field);
        $this->assertSame('=', $expression->operation);
        $this->assertSame(1, $expression->value);
    }

    public function testParseNotExpression()
    {
        $expression = Factory::parse(json_encode([
            'not' => ['user.id', '=', 1],
        ]));

        $this->assertInstanceOf(NotExpression::class, $expression);
        $this->assertInstanceOf(BinaryExpression::class, $expression->expression);
        $this->assertSame('user.id', $expression->expression->field);
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
            ],
        ]));

        $this->assertInstanceOf(AndExpression::class, $expression);
        $this->assertCount(2, $expression->expressions);
        $this->assertInstanceOf(BinaryExpression::class, $expression->expressions[0]);
        $this->assertInstanceOf(BinaryExpression::class, $expression->expressions[1]);
        $this->assertSame('user.id', $expression->expressions[0]->field);
        $this->assertSame('=', $expression->expressions[0]->operation);
        $this->assertSame(1, $expression->expressions[0]->value);
        $this->assertSame('team.id', $expression->expressions[1]->field);
        $this->assertSame('=', $expression->expressions[1]->operation);
        $this->assertSame(1, $expression->expressions[1]->value);
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
        $this->assertSame('user.id', $expression->expressions[0]->field);
        $this->assertSame('=', $expression->expressions[0]->operation);
        $this->assertSame(1, $expression->expressions[0]->value);
        $this->assertSame('team.id', $expression->expressions[1]->field);
        $this->assertSame('=', $expression->expressions[1]->operation);
        $this->assertSame(1, $expression->expressions[1]->value);
    }
}
