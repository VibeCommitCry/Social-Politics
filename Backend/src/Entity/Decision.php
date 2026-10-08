<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\DecisionStatus;
use App\Repository\DecisionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DecisionRepository::class)]
class Decision
{
    #[ORM\Id, ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'guid')]
    private string $agendaItemId;

    #[ORM\ManyToOne(targetEntity: AgendaPoint::class)]
    #[ORM\JoinColumn(name: 'agenda_point_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?AgendaPoint $agendaPoint = null;

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

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(mappedBy: 'decision', targetEntity: Vote::class, orphanRemoval: true)]
    private Collection $votes;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->votes = new ArrayCollection();
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setDecision($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        // A vote cannot exist without its decision; orphanRemoval deletes it on flush.
        $this->votes->removeElement($vote);

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAgendaPoint(): ?AgendaPoint
    {
        return $this->agendaPoint;
    }

    public function setAgendaPoint(?AgendaPoint $agendaPoint): static
    {
        $this->agendaPoint = $agendaPoint;

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
