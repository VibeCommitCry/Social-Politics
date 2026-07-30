<?php

namespace App\Entity;

use App\Enum\DecisionStatus;
use App\Enum\DecisionVoteMethod;
use App\Repository\DecisionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DecisionRepository::class)]
class Decision
{
    #[ORM\Id, ORM\Column(type: 'guid')]
    private ?string $id = null;

    #[ORM\Column(type: 'guid')]
    private string $agendaItemId;

    #[ORM\Column(enumType: DecisionVoteMethod::class, nullable: true)]
    private ?DecisionVoteMethod $voteMethod = null;

    #[ORM\Column(nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $text = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remark = null;

    #[ORM\Column(enumType: DecisionStatus::class, nullable: true)]
    private ?DecisionStatus $status = null;

    #[ORM\Column(nullable: true)]
    private ?int $sequence = null;

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

    public function getAgendaItemId(): string
    {
        return $this->agendaItemId;
    }

    public function setAgendaItemId(string $agendaItemId): static
    {
        $this->agendaItemId = $agendaItemId;

        return $this;
    }

    public function getVoteMethod(): ?DecisionVoteMethod
    {
        return $this->voteMethod;
    }

    public function setVoteMethod(?DecisionVoteMethod $voteMethod): static
    {
        $this->voteMethod = $voteMethod;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): static
    {
        $this->remark = $remark;

        return $this;
    }

    public function getStatus(): ?DecisionStatus
    {
        return $this->status;
    }

    public function setStatus(?DecisionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setSequence(?int $sequence): static
    {
        $this->sequence = $sequence;

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