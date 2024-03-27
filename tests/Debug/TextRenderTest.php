<?php

namespace Overtrue\Spectra\Tests\Debug;

use Overtrue\Spectra\Debug\TextRender;
use PHPUnit\Framework\TestCase;

class TextRenderTest extends TestCase
{
    public function testRenderWithBinaryReport()
    {
        $this->assertSame(['- [user.id]:123 = 1 -> false'], TextRender::transform(
            [
                'type' => 'BINARY',
                'result' => false,
                'expression' => [
                    'field' => 'user.id',
                    'operation' => '=',
                    'value' => 1,
                ],
            ], ['user.id' => 123]));
    }

    public function testRenderWithAndExpression()
    {
        $this->assertSame([
            '[AND]: false',
            '  - [user.id]:123 = 1 -> false',
            '  - [team.id]:123 = 123 -> true',
        ], TextRender::transform(
            [
                'type' => 'AND',
                'result' => false,
                'expressions' => [
                    [
                        'type' => 'BINARY',
                        'result' => false,
                        'expression' => [
                            'field' => 'user.id',
                            'operation' => '=',
                            'value' => 1,
                        ],
                    ],
                    [
                        'type' => 'BINARY',
                        'result' => true,
                        'expression' => [
                            'field' => 'team.id',
                            'operation' => '=',
                            'value' => 123,
                        ],
                    ],
                ],
            ], ['user.id' => 123, 'team.id' => 123]));
    }

    public function testRenderWithOrExpression()
    {
        $this->assertSame([
            '[OR]: true',
            '  - [user.id]:123 = 1 -> false',
            '  - [team.id]:123 = 123 -> true',
        ], TextRender::transform(
            [
                'type' => 'OR',
                'result' => true,
                'expressions' => [
                    [
                        'type' => 'BINARY',
                        'result' => false,
                        'expression' => [
                            'field' => 'user.id',
                            'operation' => '=',
                            'value' => 1,
                        ],
                    ],
                    [
                        'type' => 'BINARY',
                        'result' => true,
                        'expression' => [
                            'field' => 'team.id',
                            'operation' => '=',
                            'value' => 123,
                        ],
                    ],
                ],
            ], ['user.id' => 123, 'team.id' => 123]));
    }

    public function testRenderWithNotExpression()
    {
        $this->assertSame([
            '[NOT]: true',
            '  - [user.id]:123 = 1 -> false',
        ], TextRender::transform([
            'type' => 'NOT',
            'result' => true,
            'expression' => [
                [
                    'type' => 'BINARY',
                    'result' => false,
                    'expression' => [
                        'field' => 'user.id',
                        'operation' => '=',
                        'value' => 1,
                    ],
                ],
            ],
        ], ['user.id' => 123, 'team.id' => 123]));
    }

    public function testRenderWithMultiDepthExpressions()
    {
        $this->assertSame([
            '[AND]: true',
            '  - [user.id]:123 = 123 -> true',
            '  - [user.id]:123 >= 123 -> true',
            '  [OR]: true',
            '    - [file.id]:2 = 1 -> false',
            '    [NOT]: true',
            '      - [file.is_draft]:false = true -> false',
        ], TextRender::transform([
            'type' => 'AND',
            'result' => true,
            'expressions' => [
                [
                    'type' => 'BINARY',
                    'result' => true,
                    'expression' => [
                        'field' => 'user.id',
                        'operation' => '=',
                        'value' => 123,
                    ],
                ],
                [
                    'type' => 'BINARY',
                    'result' => true,
                    'expression' => [
                        'field' => 'user.id',
                        'operation' => '>=',
                        'value' => 123,
                    ],
                ],
                [
                    'type' => 'OR',
                    'result' => true,
                    'expressions' => [
                        [
                            'type' => 'BINARY',
                            'result' => false,
                            'expression' => [
                                'field' => 'file.id',
                                'operation' => '=',
                                'value' => 1,
                            ],
                        ],
                        [
                            'type' => 'NOT',
                            'result' => true,
                            'expression' => [
                                'type' => 'BINARY',
                                'result' => false,
                                'expression' => [
                                    'field' => 'file.is_draft',
                                    'operation' => '=',
                                    'value' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], ['user.id' => 123, 'team.id' => 123, 'file.id' => 2, 'file.is_draft' => false]));
    }
}
