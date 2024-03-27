<?php

namespace Overtrue\Spectra\Tests\Expressions;

use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\OrExpression;
use PHPUnit\Framework\TestCase;

class OrExpressionTest extends TestCase
{
    public function testEvaluate()
    {
        $true1 = new BinaryExpression('user.id', '=', 1);
        $true2 = new BinaryExpression('user.id', '=', 1);
        $false1 = new BinaryExpression('user.id', '!=', 1);
        $false2 = new BinaryExpression('user.id', '!=', 1);

        // true, true
        $this->assertTrue((new OrExpression([$true1, $true2]))->evaluate(['user.id' => 1]));

        // true, false
        $this->assertTrue((new OrExpression([$true1, $false1]))->evaluate(['user.id' => 1]));

        // false, false
        $this->assertFalse((new OrExpression([$false1, $false2]))->evaluate(['user.id' => 1]));

        // true, true, false
        $this->assertTrue((new OrExpression([$true1, $true2, $false1]))->evaluate(['user.id' => 1]));
    }

    public function testSerialize()
    {
        $expression1 = new BinaryExpression('team.id', '=', 123);
        $expression2 = new BinaryExpression('user.id', '!=', null);

        // to string
        $this->assertSame('{"or":[["team.id","=",123]]}', (string) (new OrExpression([$expression1])));

        // json
        $this->assertSame('{"or":[["team.id","=",123]]}', json_encode(new OrExpression([$expression1])));
        $this->assertSame('{"or":[["team.id","=",123],["user.id","!=",null]]}', json_encode(new OrExpression([$expression1, $expression2])));
    }
}
