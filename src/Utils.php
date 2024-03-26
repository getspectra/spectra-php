<?php

namespace Overtrue\Spectra;

class Utils
{
    public static function getClassBasename(string $class): string
    {
        return basename(str_replace('\\', '/', __CLASS__));
    }

    public static function arrayGet(array $array, string|int $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (!is_string($key) || !str_contains($key, '.')) {
            return $default;
        }

        $items = $array;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($items) || !array_key_exists($segment, $items)) {
                return $default;
            }

            $items = &$items[$segment];
        }

        return $items;
    }
}