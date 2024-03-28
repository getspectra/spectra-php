<?php

namespace Overtrue\Spectra;

class Field implements \JsonSerializable
{
    public function __construct(public string $name)
    {
    }

    protected function getName(): string
    {
        return $this->name;
    }

    public function evaluate(array $data)
    {
        return Utils::arrayGet($data, $this->name);
    }

    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }

    public function jsonSerialize(): mixed
    {
        return $this->name;
    }
}
