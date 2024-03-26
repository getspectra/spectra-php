<?php

namespace Overtrue\Spectra\Tests;

use Overtrue\Spectra\Operation;
use PHPUnit\Framework\TestCase;

class OperationTest extends TestCase
{
    public function testEqualOperation()
    {
        $this->assertTrue(Operation::EQ->operate(10, 10));
        $this->assertTrue(Operation::EQ->operate('hello', 'hello'));
        $this->assertTrue(Operation::EQ->operate(true, true));
        $this->assertTrue(Operation::EQ->operate(null, null));
        $this->assertTrue(Operation::EQ->operate([1, 2, 3], [1, 2, 3]));
        $this->assertTrue(Operation::EQ->operate((object) ['a' => 1], (object) ['a' => 1]));

        $this->assertFalse(Operation::EQ->operate(10, 20));
        $this->assertFalse(Operation::EQ->operate('hello', 'world'));
        $this->assertFalse(Operation::EQ->operate(true, false));
        $this->assertFalse(Operation::EQ->operate(null, 1));
        $this->assertFalse(Operation::EQ->operate([1, 2], [1, 2, 3]));
        $this->assertFalse(Operation::EQ->operate((object) ['a' => 1], (object) ['a' => 2]));
    }

    public function testNotEqualOperation()
    {
        $this->assertTrue(Operation::NEQ->operate(10, 20));
        $this->assertTrue(Operation::NEQ->operate('hello', 'world'));
        $this->assertTrue(Operation::NEQ->operate(true, false));
        $this->assertTrue(Operation::NEQ->operate(null, 1));
        $this->assertTrue(Operation::NEQ->operate([1, 2], [1, 2, 3]));
        $this->assertTrue(Operation::NEQ->operate((object) ['a' => 1], (object) ['a' => 2]));

        $this->assertFalse(Operation::NEQ->operate(10, 10));
        $this->assertFalse(Operation::NEQ->operate('hello', 'hello'));
        $this->assertFalse(Operation::NEQ->operate(true, true));
        $this->assertFalse(Operation::NEQ->operate(null, null));
        $this->assertFalse(Operation::NEQ->operate([1, 2, 3], [1, 2, 3]));
        $this->assertFalse(Operation::NEQ->operate((object) ['a' => 1], (object) ['a' => 1]));
    }

    public function testGreaterThanOperation()
    {
        $this->assertTrue(Operation::GT->operate(20, 10));
        $this->assertTrue(Operation::GT->operate('world', 'hello'));

        $this->assertFalse(Operation::GT->operate(10, 20));
        $this->assertFalse(Operation::GT->operate('hello', 'world'));
        $this->assertFalse(Operation::GT->operate(10, 10));
    }

    public function testGreaterThanOrEqualOperation()
    {
        $this->assertTrue(Operation::GTE->operate(10, 10));
        $this->assertTrue(Operation::GTE->operate('hello', 'hello'));
        $this->assertTrue(Operation::GTE->operate(20, 10));

        $this->assertFalse(Operation::GTE->operate(10, 20));
        $this->assertFalse(Operation::GTE->operate('hello', 'world'));
    }

    public function testLessThanOperation()
    {
        $this->assertTrue(Operation::LT->operate(10, 20));
        $this->assertTrue(Operation::LT->operate('hello', 'world'));

        $this->assertFalse(Operation::LT->operate(20, 10));
        $this->assertFalse(Operation::LT->operate('world', 'hello'));
        $this->assertFalse(Operation::LT->operate(10, 10));
    }

    public function testLessThanOrEqualOperation()
    {
        $this->assertTrue(Operation::LTE->operate(10, 10));
        $this->assertTrue(Operation::LTE->operate('hello', 'hello'));
        $this->assertTrue(Operation::LTE->operate(10, 20));

        $this->assertFalse(Operation::LTE->operate(20, 10));
        $this->assertFalse(Operation::LTE->operate('world', 'hello'));
    }

    public function testInOperation()
    {
        $this->assertTrue(Operation::IN->operate(1, [1, 2, 3]));
        $this->assertTrue(Operation::IN->operate('a', ['a', 'b', 'c']));

        $this->assertFalse(Operation::IN->operate(4, [1, 2, 3]));
        $this->assertFalse(Operation::IN->operate('d', ['a', 'b', 'c']));
        $this->assertFalse(Operation::IN->operate(1, [2, 3, 4]));
        $this->assertFalse(Operation::IN->operate('a', ['b', 'c', 'd']));
    }

    public function testNotInOperation()
    {
        $this->assertTrue(Operation::NIN->operate(4, [1, 2, 3]));
        $this->assertTrue(Operation::NIN->operate('d', ['a', 'b', 'c']));

        $this->assertFalse(Operation::NIN->operate(1, [1, 2, 3]));
        $this->assertFalse(Operation::NIN->operate('a', ['a', 'b', 'c']));
        $this->assertFalse(Operation::NIN->operate(2, [1, 2, 3]));
        $this->assertFalse(Operation::NIN->operate('b', ['a', 'b', 'c']));
    }
}
