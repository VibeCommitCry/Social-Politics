<?php

declare(strict_types=1);

namespace App\Interface;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.importer.tag')]
interface ImporterInterface
{
    public function supports(string $dtoClass): bool;
    public function import(DtoInterface $dto): void;
}
