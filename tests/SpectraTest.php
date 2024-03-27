<?php

namespace Overtrue\Spectra\Tests;

use Overtrue\Spectra\DataLoaders\ArrayDataLoader;
use Overtrue\Spectra\Effect;
use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Expressions\Factory;
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

    public function testDebug()
    {
        $polices = [new Policy(Factory::and([['user.id', '=', 1], ['team.id', '=', 1]]), 'allow', ['EDIT_FILE'], 'User 1 can edit file.')];

        $loader = new ArrayDataLoader([
            'user.id' => 1,
            'user.team_id' => 1,
            'team.status' => 'active',
        ]);

        $permissionName = 'EDIT_FILE';

        $report = Spectra::debug($polices, $loader, $permissionName);
    }
}
