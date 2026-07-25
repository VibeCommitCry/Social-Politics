<?php

namespace App\Interface;


interface ImportDtoInterface
{
    public function saveDtoAsEntity(DtoInterface $dto): void;
    public function flush(): void;
}