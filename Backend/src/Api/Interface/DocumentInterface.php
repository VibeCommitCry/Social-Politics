<?php

namespace App\Api\Interface;

use App\Api\Dto\DocumentDto;
use App\Api\Filter\DocumentFilter;

interface DocumentInterface
{
    /**
     * @param DocumentFilter $filter
     * @return DocumentDto[]
     */
    public function search(DocumentFilter $filter): array;
}