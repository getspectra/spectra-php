<?php

namespace Overtrue\Spectra\Tests\Expressions;

use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Ref;
use Overtrue\Spectra\RefField;
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
        $expression = new BinaryExpression('user.team_id', '=', new RefField('team.id'));

        $this->assertTrue($expression->evaluate(['user' => ['team_id' => 1], 'team' => ['id' => 1]]));

        $expression = new BinaryExpression('user.team_id', '=', new RefField('team.not_exists_key'));

        $this->assertFalse($expression->evaluate(['user' => ['team_id' => 1], 'team' => ['id' => 1]]));
    }

    public function testSerialize()
    {
        // to string
        $this->assertSame('["foo","=","foo"]', (string) (new BinaryExpression('foo', '=', 'foo')));

        // json
        $this->assertSame('["foo","=","foo"]', json_encode(new BinaryExpression('foo', '=', 'foo')));
        $this->assertSame('["user.id","=",1000]', json_encode(new BinaryExpression('user.id', '=', 1000)));
        $this->assertSame('["user.team_id","<>",null]', json_encode(new BinaryExpression('user.team_id', '<>', null)));
        $this->assertSame('["user.age",">=",18]', json_encode(new BinaryExpression('user.age', '>=', 18)));
        $this->assertSame('["user.status","in",["unactivated","banned"]]', json_encode(new BinaryExpression('user.status', 'in', ['unactivated', 'banned'])));
        $this->assertSame('["user.status","nin",["unactivated","banned"]]', json_encode(new BinaryExpression('user.status', 'nin', ['unactivated', 'banned'])));

        // ref
        $this->assertSame('["user.team_id","=",{"ref":"team.id"}]', json_encode(new BinaryExpression('user.team_id', '=', new RefField('team.id'))));
    }
}
