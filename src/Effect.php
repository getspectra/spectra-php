<?php

namespace Overtrue\Spectra;

enum Effect: string
{
    case ALLOW = 'allow';
    case DENY = 'deny';
}
