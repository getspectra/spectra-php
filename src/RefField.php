<?php

namespace Overtrue\Spectra;

class RefField extends Field
{
    public function jsonSerialize(): array
    {
        return ['ref' => $this->name];
    }
}
