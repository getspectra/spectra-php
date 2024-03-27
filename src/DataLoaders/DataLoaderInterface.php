<?php

namespace Overtrue\Spectra\DataLoaders;

interface DataLoaderInterface
{
    /**
     * @return array<string, mixed> $fields
     */
    public function load(array $fields): array;
}
