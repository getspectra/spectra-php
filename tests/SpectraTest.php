<?php

namespace Overtrue\Spectra\Tests;

use Overtrue\Spectra\DataLoaders\ArrayDataLoader;
use Overtrue\Spectra\Effect;
use Overtrue\Spectra\Expressions\AndExpression;
use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\NotExpression;
use Overtrue\Spectra\Expressions\OrExpression;
use Overtrue\Spectra\Polices\Policy;
use Overtrue\Spectra\Spectra;

class SpectraTest extends TestCase
{
    public function testValidate()
    {
        $allowPolicy = new Policy(new BinaryExpression('user.id', '=', 1), Effect::ALLOW, ['EDIT_FILE']);
        $denyPolicy = new Policy(new BinaryExpression('user.id', '=', 1), Effect::DENY, ['EDIT_FILE']);

        // DENY has higher priority than ALLOW
        $this->assertFalse(Spectra::validate([$allowPolicy, $denyPolicy], new ArrayDataLoader(['user.id' => 1]), 'EDIT_FILE'));

        $allowPolicy = new Policy(new BinaryExpression('user.id', '=', 1), Effect::ALLOW, ['EDIT_FILE']);
        $denyPolicy = new Policy(new BinaryExpression('user.id', '=', 2), Effect::DENY, ['EDIT_FILE']);

        // user.id = 1: allow
        // user.id = 2: deny
        $this->assertTrue(Spectra::validate([$allowPolicy, $denyPolicy], new ArrayDataLoader(['user.id' => 1]), 'EDIT_FILE'));
        $this->assertFalse(Spectra::validate([$allowPolicy, $denyPolicy], new ArrayDataLoader(['user.id' => 2]), 'EDIT_FILE'));
    }

    public function testMatch()
    {
        $expression = new AndExpression([
            new BinaryExpression('user.id', '=', 1),
            new BinaryExpression('team.id', '=', 1),
        ]);

        $policy = new Policy($expression, Effect::ALLOW, ['EDIT_FILE']);

        $this->assertTrue(Spectra::match($policy, ['user' => ['id' => 1], 'team' => ['id' => 1]]));
        $this->assertFalse(Spectra::match($policy, ['user' => ['id' => 1], 'team' => ['id' => 2]]));
        $this->assertFalse(Spectra::match($policy, ['user' => ['id' => 2], 'team' => ['id' => 1]]));
    }

    public function testParseBinaryExpression()
    {
        $expression = Spectra::parseExpression(json_encode(['user.id', '=', 1]));

        $this->assertInstanceOf(BinaryExpression::class, $expression);

        $this->assertSame('user.id', $expression->field);
        $this->assertSame('=', $expression->operation);
        $this->assertSame(1, $expression->value);
    }

    public function testParseNotExpression()
    {
        $expression = Spectra::parseExpression(json_encode([
            'not' => ['user.id', '=', 1],
        ]));

        $this->assertInstanceOf(NotExpression::class, $expression);
        $this->assertInstanceOf(BinaryExpression::class, $expression->expression);
        $this->assertSame('user.id', $expression->expression->field);
        $this->assertSame('=', $expression->expression->operation);

        // also support array
        $this->assertEquals($expression, Spectra::parseExpression([
            'not' => ['user.id', '=', 1],
        ]));
    }

    public function testParseAndExpression()
    {
        $expression = Spectra::parseExpression(json_encode([
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
        $expression = Spectra::parseExpression(json_encode([
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
