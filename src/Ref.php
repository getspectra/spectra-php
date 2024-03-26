<?php

namespace Overtrue\Spectra;

class Ref
{
    public function __construct(public string $field)
    {
    }

    public function toValue(array $data)
    {
        return Utils::arrayGet($data, $this->field);
    }

    public function __toString()
    {
        return json_encode(['ref' => $this->field]);
    }
}