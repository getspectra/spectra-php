<?php

namespace Overtrue\Spectra\Debug;

use Overtrue\Spectra\Ref;
use Overtrue\Spectra\Utils;

class TextRender implements RenderInterface
{
    public static function render(array $report): string
    {
        $lines = [];

        foreach ($report as $policy) {
            $lines[] = sprintf('[%s][%s] Filter %s: ', $policy['description'], $policy['effect'], $policy['matched'] ? 'passed' : 'failed');
            $lines = array_merge($lines, self::transform($policy['expressions'], $policy['data'], 2));
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    public static function transform(array $item, array $data, int $intend = 0): array
    {
        $lines = [];

        if (array_is_list($item)) {
            $subLines = [];
            foreach ($item as $subItem) {
                $subLines = array_merge($subLines, self::transform($subItem, $data, $intend));
            }

            return $subLines;
        }

        assert(array_key_exists('type', $item));
        assert(array_key_exists('result', $item));

        switch ($item['type']) {
            case 'AND':
            case 'OR':
                $lines[] = sprintf('[%s]: %s', $item['type'], Utils::valueToString($item['result']));
                $subLines = self::transform($item['expressions'], $data);
                $lines = array_merge($lines, array_map(fn ($line) => Utils::strIntend($line, $intend + 2), $subLines));
                break;
            case 'NOT':
                $lines[] = sprintf('[%s]: %s', $item['type'], Utils::valueToString($item['result']));
                $lines = array_merge($lines, self::transform($item['expression'], $data, $intend + 2));
                break;
            case 'BINARY':
                $fieldValue = Utils::arrayGet($data, $item['expression']['field']);
                $value = $item['expression']['value'];

                if ($value instanceof Ref) {
                    $value = sprintf('%s:%s', $value, Utils::valueToString($value->toValue($data)));
                } else {
                    $value = Utils::valueToString($value);
                }

                $lines[] = sprintf(
                    '- [%s]:%s %s %s -> %s',
                    $item['expression']['field'], Utils::valueToString($fieldValue),
                    $item['expression']['operation'],
                    $value, Utils::valueToString($item['result']),
                );
                break;
            default:
                $lines[] = sprintf('- [%s]:%s', $item['expression']['class'], Utils::valueToString($item['result']));
                break;
        }

        return array_map(fn ($line) => Utils::strIntend($line, $intend), $lines);
    }
}
