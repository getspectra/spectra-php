<?php

namespace Overtrue\Spectra\Tests\Expressions;

use Overtrue\Spectra\Expressions\AndExpression;
use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\NotExpression;
use PHPUnit\Framework\TestCase;

class NotExpressionTest extends TestCase
{
    public function testEvaluate()
    {
        $true = new BinaryExpression('user.id', '=', 1);
        $false = new BinaryExpression('user.id', '!=', 1);

        // true
        $this->assertFalse((new NotExpression($true))->evaluate(['user.id' => 1]));

        // false
        $this->assertTrue((new NotExpression($false))->evaluate(['user.id' => 1]));
    }

    public function testSerialize()
    {
        $expression1 = new BinaryExpression('team.id', '=', 123);
        $expression2 = new BinaryExpression('user.id', '!=', null);

        // to string
        $this->assertSame('{"not":["team.id","=",123]}', (string) (new NotExpression($expression1)));

        // json
        $this->assertSame('{"not":["team.id","=",123]}', json_encode(new NotExpression($expression1)));
        $this->assertSame('{"not":{"and":[["team.id","=",123],["user.id","!=",null]]}}', json_encode(new NotExpression(new AndExpression([$expression1, $expression2]))));
    }
}
