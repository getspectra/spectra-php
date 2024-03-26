<?php

namespace Overtrue\Spectra\DataLoaders;

interface DataLoaderInterface
{
    /**
     * @return array<string, string> $fields
     */
    public function load(array $fields): array;
}
