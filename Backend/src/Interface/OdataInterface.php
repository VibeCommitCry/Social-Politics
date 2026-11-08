<?php

declare(strict_types=1);

namespace App\Interface;

use Generator;

interface OdataInterface
{
    /**
     * @template T of DtoInterface
     *
     * @param FilterInterface<T> $filter
     *
     * @return Generator<int, T>
     */
    public function search(FilterInterface $filter): Generator;

}
