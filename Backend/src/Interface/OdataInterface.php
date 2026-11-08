<?php

declare(strict_types=1);

namespace App\Interface;

use Generator;

interface OdataInterface
{
    /**
     * @template T of DtoInterface
     *
     * @return Generator<int, T>
     */
    public function search(FilterInterface $filter): Generator;

}
