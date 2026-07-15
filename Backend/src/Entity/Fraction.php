<?php

namespace App\Entity;

use App\Repository\FractionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FractionRepository::class)]
class Fraction
{
    #[ORM\Id, ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(nullable: true)]
    private ?string $abbreviation = null;

    #[ORM\Column(nullable: true)]
    private ?string $nameNl = null;

    #[ORM\Column(nullable: true)]
    private ?string $nameEn = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $seatCount = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $voteCount = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $activeFrom = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $activeUntil = null;

    #[ORM\Column(type: 'boolean')]
    private bool $deleted = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(?string $abbreviation): static
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    public function getNameNl(): ?string
    {
        return $this->nameNl;
    }

    public function setNameNl(?string $nameNl): static
    {
        $this->nameNl = $nameNl;
        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(?string $nameEn): static
    {
        $this->nameEn = $nameEn;
        return $this;
    }

    public function getSeatCount(): ?int
    {
        return $this->seatCount;
    }

    public function setSeatCount(?int $seatCount): static
    {
        $this->seatCount = $seatCount;
        return $this;
    }

    public function getVoteCount(): ?int
    {
        return $this->voteCount;
    }

    public function setVoteCount(?int $voteCount): static
    {
        $this->voteCount = $voteCount;
        return $this;
    }

    public function getActiveFrom(): ?\DateTimeImmutable
    {
        return $this->activeFrom;
    }

    public function setActiveFrom(?\DateTimeImmutable $activeFrom): static
    {
        $this->activeFrom = $activeFrom;
        return $this;
    }

    public function getActiveUntil(): ?\DateTimeImmutable
    {
        return $this->activeUntil;
    }

    public function setActiveUntil(?\DateTimeImmutable $activeUntil): static
    {
        $this->activeUntil = $activeUntil;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}