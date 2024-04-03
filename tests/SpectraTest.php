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

        $data = [
            'user.id' => 1,
            'team.id' => 1,
            'team.status' => 'active',
        ];
        $loader = new ArrayDataLoader($data);

        $permissionName = 'EDIT_FILE';

        $report = Spectra::debug($polices, $loader, $permissionName);

        $this->assertSame([
            'permission' => 'EDIT_FILE',
            'policies' => [
                [
                    'description' => 'User 1 can edit file.',
                    'effect' => 'allow',
                    'permissions' => ['EDIT_FILE'],
                    'fields' => ['user.id', 'team.id'],
                    'applied' => true,
                    'matched' => true,
                    'filter' => [
                        'name' => 'And',
                        'value' => true,
                        'expressions' => [
                            [
                                'name' => 'Binary',
                                'value' => true,
                                'expression' => [
                                    'operation' => '=',
                                    'left' => ['name' => 'user.id', 'value' => 1],
                                    'right' => ['name' => null, 'value' => 1],
                                ],
                            ],
                            [
                                'name' => 'Binary',
                                'value' => true,
                                'expression' => [
                                    'operation' => '=',
                                    'left' => ['name' => 'team.id', 'value' => 1],
                                    'right' => ['name' => null, 'value' => 1],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'fields' => ['user.id', 'team.id'],
            'data' => $data,
        ], $report);
    }
}
