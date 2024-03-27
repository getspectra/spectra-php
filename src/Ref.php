<?php

namespace Overtrue\Spectra;

class Ref implements \JsonSerializable
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
        return json_encode($this->jsonSerialize());
    }

    public function jsonSerialize(): mixed
    {
        return ['ref' => $this->field];
    }
}
