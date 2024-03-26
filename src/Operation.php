<?php

namespace Overtrue\Spectra;

enum Operation: string
{
    case EQ = '=';
    case NEQ = '!=';
    case NEQ2 = '<>';
    case GT = '>';
    case GTE = '>=';
    case LT = '<';
    case LTE = '<=';
    case IN = 'in';
    case NIN = 'nin';

    public function operate($leftValue, $rightValue): bool
    {
        return match ($this) {
            self::EQ => $leftValue == $rightValue,
            self::NEQ, self::NEQ2 => $leftValue != $rightValue,
            self::GT => $leftValue > $rightValue,
            self::GTE => $leftValue >= $rightValue,
            self::LT => $leftValue < $rightValue,
            self::LTE => $leftValue <= $rightValue,
            self::IN => in_array($leftValue, $rightValue, true),
            self::NIN => ! in_array($leftValue, $rightValue, true),
            default => false,
        };
    }
}
