<?php

namespace Overtrue\Spectra\Tests\Polices;

use Overtrue\Spectra\Effect;
use Overtrue\Spectra\Expressions\AndExpression;
use Overtrue\Spectra\Expressions\BinaryExpression;
use Overtrue\Spectra\Polices\Policy;
use PHPUnit\Framework\TestCase;

class PolicyTest extends TestCase
{
    public function testSerialize()
    {
        $this->assertSame(json_encode([
            'description' => 'test',
            'effect' => 'allow',
            'permissions' => ['EDIT_FILE'],
            'apply_filter' => ['user.id', '=', 1],
            'fields' => ['user.id'],
        ]), json_encode(new Policy(new BinaryExpression('user.id', '=', 1), 'ALLOW', ['EDIT_FILE'], 'test')));
    }

    public function testParse()
    {
        $policy = Policy::parse(json_encode([
            'description' => 'test',
            'effect' => 'allow',
            'permissions' => ['EDIT_FILE'],
            'apply_filter' => ['user.id', '=', 1],
            'fields' => ['user.id'],
        ]));

        $this->assertSame('test', $policy->getDescription());
        $this->assertSame('allow', $policy->getEffect()->value);
        $this->assertSame(['EDIT_FILE'], $policy->getPermissions());
        $this->assertSame(['user.id'], $policy->getFields());
        $this->assertInstanceOf(BinaryExpression::class, $policy->getApplyFilter());
    }

    public function testApply()
    {
        $expression = new AndExpression([
            new BinaryExpression('user.id', '=', 1),
            new BinaryExpression('team.id', '=', 1),
        ]);

        $policy = new Policy($expression, Effect::ALLOW, ['EDIT_FILE']);

        $this->assertTrue($policy->apply(['user' => ['id' => 1], 'team' => ['id' => 1]]));
        $this->assertFalse($policy->apply(['user' => ['id' => 1], 'team' => ['id' => 2]]));
        $this->assertFalse($policy->apply(['user' => ['id' => 2], 'team' => ['id' => 1]]));
    }
}
