<?php

namespace Overtrue\Spectra\Tests\Expressions;

use Overtrue\Spectra\Expressions\AndExpression;
use Overtrue\Spectra\Expressions\BinaryExpression;
use PHPUnit\Framework\TestCase;

class AndExpressionTest extends TestCase
{
    public function testEvaluate()
    {
        $true1 = new BinaryExpression('user.id', '=', 1);
        $true2 = new BinaryExpression('user.id', '=', 1);
        $false1 = new BinaryExpression('user.id', '!=', 1);
        $false2 = new BinaryExpression('user.id', '!=', 1);

        // true, true
        $this->assertTrue((new AndExpression([$true1, $true2]))->evaluate(['user.id' => 1]));

        // true, false
        $this->assertFalse((new AndExpression([$true1, $false1]))->evaluate(['user.id' => 1]));

        // false, true
        $this->assertFalse((new AndExpression([$false1, $false2]))->evaluate(['user.id' => 1]));

        // false, false
        $this->assertFalse((new AndExpression([$false1, $false1]))->evaluate(['user.id' => 1]));

        // true, true, false
        $this->assertFalse((new AndExpression([$true1, $true2, $false1]))->evaluate(['user.id' => 1]));
    }

    public function testGetFields()
    {
        $this->assertEquals(['user.id'], (new AndExpression([
            new BinaryExpression('user.id', '=', 1),
        ]))->getFields());

        $this->assertEquals(['user.id', 'user.name'], (new AndExpression([
            new BinaryExpression('user.id', '=', 1),
            new BinaryExpression('user.name', '=', 'overtrue'),
        ]))->getFields());

        $this->assertEquals(['user.id', 'user.name', 'user.age'], (new AndExpression([
            new BinaryExpression('user.id', '=', 1),
            new BinaryExpression('user.name', '=', 'overtrue'),
            new BinaryExpression('user.age', '=', 18),
        ]))->getFields());

        $this->assertEquals(['user.id', 'user.name', 'team.creator_id', 'file.user_id'], (new AndExpression([
            new BinaryExpression('user.id', '=', 1),
            new BinaryExpression('user.name', '=', 'overtrue'),
            new AndExpression([
                new BinaryExpression('team.creator_id', '=', 18),
                new BinaryExpression('file.user_id', '=', 1),
            ]),
        ]))->getFields());
    }
}
