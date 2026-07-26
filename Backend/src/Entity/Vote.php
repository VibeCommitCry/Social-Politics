<?php

namespace App\Entity;

use App\Enum\VoteType;
use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
class Vote
{
    #[ORM\Id, ORM\Column(type: 'guid')]
    private ?string $id = null;

    #[ORM\Column(type: 'guid')]
    private string $decisionId;

    #[ORM\Column(enumType: VoteType::class, nullable: true)]
    private ?VoteType $type = null;

    #[ORM\Column(nullable: true)]
    private ?int $fractionSize = null;

    #[ORM\Column(nullable: true)]
    private ?string $actorName = null;

    #[ORM\Column(nullable: true)]
    private ?string $fractionName = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $mistake = null;

    #[ORM\Column(type: 'guid', nullable: true)]
    private ?string $personId = null;

    #[ORM\Column(type: 'guid', nullable: true)]
    private ?string $fractionId = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $modifiedOn = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $apiModifiedOn = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $deleted = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDecisionId(): string
    {
        return $this->decisionId;
    }

    public function setDecisionId(string $decisionId): static
    {
        $this->decisionId = $decisionId;

        return $this;
    }

    public function getType(): ?VoteType
    {
        return $this->type;
    }

    public function setType(?VoteType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getFractionSize(): ?int
    {
        return $this->fractionSize;
    }

    public function setFractionSize(?int $fractionSize): static
    {
        $this->fractionSize = $fractionSize;

        return $this;
    }

    public function getActorName(): ?string
    {
        return $this->actorName;
    }

    public function setActorName(?string $actorName): static
    {
        $this->actorName = $actorName;

        return $this;
    }

    public function getFractionName(): ?string
    {
        return $this->fractionName;
    }

    public function setFractionName(?string $fractionName): static
    {
        $this->fractionName = $fractionName;

        return $this;
    }

    public function isMistake(): ?bool
    {
        return $this->mistake;
    }

    public function setMistake(?bool $mistake): static
    {
        $this->mistake = $mistake;

        return $this;
    }

    public function getPersonId(): ?string
    {
        return $this->personId;
    }

    public function setPersonId(?string $personId): static
    {
        $this->personId = $personId;

        return $this;
    }

    public function getFractionId(): ?string
    {
        return $this->fractionId;
    }

    public function setFractionId(?string $fractionId): static
    {
        $this->fractionId = $fractionId;

        return $this;
    }

    public function getModifiedOn(): ?\DateTimeImmutable
    {
        return $this->modifiedOn;
    }

    public function setModifiedOn(?\DateTimeImmutable $modifiedOn): static
    {
        $this->modifiedOn = $modifiedOn;

        return $this;
    }

    public function getApiModifiedOn(): ?\DateTimeImmutable
    {
        return $this->apiModifiedOn;
    }

    public function setApiModifiedOn(?\DateTimeImmutable $apiModifiedOn): static
    {
        $this->apiModifiedOn = $apiModifiedOn;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }
}