<?php

namespace Overtrue\Spectra\Tests\Expressions;

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
}
