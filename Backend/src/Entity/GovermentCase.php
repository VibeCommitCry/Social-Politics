<?php

namespace App\Entity;

use App\Repository\GovermentCaseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GovermentCaseRepository::class)]
class GovermentCase
{
    #[ORM\Id, ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $number = null;         
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;             

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $title = null;            

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $subject = null;        

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $startedOn = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $organization = null;     
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $currentStatus = null;    

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $cabinetPosition = null;  

    #[ORM\Column(type: 'boolean')]
    private bool $completed = false;         

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

    public function getNumber(): ?string { return $this->number; }
    public function setNumber(?string $number): static { $this->number = $number; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): static { $this->type = $type; return $this; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): static { $this->title = $title; return $this; }

    public function getSubject(): ?string { return $this->subject; }
    public function setSubject(?string $subject): static { $this->subject = $subject; return $this; }

    public function getStartedOn(): ?\DateTimeImmutable { return $this->startedOn; }
    public function setStartedOn(?\DateTimeImmutable $startedOn): static { $this->startedOn = $startedOn; return $this; }

    public function getOrganization(): ?string { return $this->organization; }
    public function setOrganization(?string $organization): static { $this->organization = $organization; return $this; }

    public function getCurrentStatus(): ?string { return $this->currentStatus; }
    public function setCurrentStatus(?string $currentStatus): static { $this->currentStatus = $currentStatus; return $this; }

    public function getCabinetPosition(): ?string { return $this->cabinetPosition; }
    public function setCabinetPosition(?string $cabinetPosition): static { $this->cabinetPosition = $cabinetPosition; return $this; }

    public function isCompleted(): bool { return $this->completed; }
    public function setCompleted(bool $completed): static { $this->completed = $completed; return $this; }

    public function isDeleted(): bool { return $this->deleted; }
    public function setDeleted(bool $deleted): static { $this->deleted = $deleted; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
}