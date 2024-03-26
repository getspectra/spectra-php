<?php

namespace Overtrue\Spectra\Tests;

use Overtrue\Spectra\DataLoaders\ArrayDataLoader;
use Overtrue\Spectra\Effect;
use Overtrue\Spectra\Expressions\AndExpression;
use Overtrue\Spectra\Expressions\BinaryExpression;
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
}