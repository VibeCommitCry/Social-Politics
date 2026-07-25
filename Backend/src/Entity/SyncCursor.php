<?php

namespace App\Entity;

use App\Repository\SyncCursorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SyncCursorRepository::class)]
class SyncCursor
{
    #[ORM\Id, ORM\Column(length: 64)]
    private string $category;

    #[ORM\Column(type: 'text')]
    private string $nextUrl;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $category, string $governmentApiBaseUrl)
    {
        $this->category = $category;
        $this->nextUrl = $governmentApiBaseUrl;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getNextUrl(): string
    {
        return $this->nextUrl;
    }

    public function setNextUrl(string $url): void
    {
        $this->nextUrl = $url;
        $this->updatedAt = new \DateTimeImmutable();
    }
}