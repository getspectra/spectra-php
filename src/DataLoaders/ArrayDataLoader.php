<?php

namespace Overtrue\Spectra\DataLoaders;

class ArrayDataLoader implements DataLoaderInterface
{
    public function __construct(public array $data)
    {
    }

    public function load(array $fields): array
    {
        return $this->data;
    }
}
