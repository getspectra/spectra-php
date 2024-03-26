<?php

namespace Overtrue\Spectra\Tests\Expressions;

use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Ref;
use PHPUnit\Framework\TestCase;

class BinaryExpressionTest extends TestCase
{
    public function testEvaluate()
    {
        $this->assertTrue((new BinaryExpression('foo', '=', 'foo'))->evaluate(['foo' => 'foo']));
        $this->assertFalse((new BinaryExpression('foo', '=', 'bar'))->evaluate(['foo' => 'foo']));
        $this->assertTrue((new BinaryExpression('foo', '!=', 'bar'))->evaluate(['foo' => 'foo']));
        $this->assertFalse((new BinaryExpression('foo', '!=', 'foo'))->evaluate(['foo' => 'foo']));
    }

    public function testEvaluateWithRef()
    {
        $expression = new BinaryExpression('user.team_id', '=', new Ref('team.id'));

        $this->assertTrue($expression->evaluate(['user' => ['team_id' => 1], 'team' => ['id' => 1]]));

        $expression = new BinaryExpression('user.team_id', '=', new Ref('team.not_exists_key'));

        $this->assertFalse($expression->evaluate(['user' => ['team_id' => 1], 'team' => ['id' => 1]]));
    }
}
