<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id, ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $number = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $dateType = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $private = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $parliamentaryYear = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $room = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remark = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $leadCommitteeName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $leadCommitteeAbbreviation = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $leadCommitteeShortName = null;

    #[ORM\Column(type: 'guid', nullable: true)]
    private ?string $leadCommitteeId = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $requestDate = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $firstExtensionRequestDate = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $firstExtensionNoticeDate = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $secondExtensionRequestDate = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $secondExtensionNoticeDate = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expiryDate = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $modifiedOn = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $apiModifiedOn = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $deleted = null;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
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

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): static
    {
        $this->number = $number;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getDateType(): ?string
    {
        return $this->dateType;
    }

    public function setDateType(?string $dateType): static
    {
        $this->dateType = $dateType;
        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(?bool $private): static
    {
        $this->private = $private;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getParliamentaryYear(): ?string
    {
        return $this->parliamentaryYear;
    }

    public function setParliamentaryYear(?string $parliamentaryYear): static
    {
        $this->parliamentaryYear = $parliamentaryYear;
        return $this;
    }

    public function getRoom(): ?string
    {
        return $this->room;
    }

    public function setRoom(?string $room): static
    {
        $this->room = $room;
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

    public function getLeadCommitteeName(): ?string
    {
        return $this->leadCommitteeName;
    }

    public function setLeadCommitteeName(?string $leadCommitteeName): static
    {
        $this->leadCommitteeName = $leadCommitteeName;
        return $this;
    }

    public function getLeadCommitteeAbbreviation(): ?string
    {
        return $this->leadCommitteeAbbreviation;
    }

    public function setLeadCommitteeAbbreviation(?string $leadCommitteeAbbreviation): static
    {
        $this->leadCommitteeAbbreviation = $leadCommitteeAbbreviation;
        return $this;
    }

    public function getLeadCommitteeShortName(): ?string
    {
        return $this->leadCommitteeShortName;
    }

    public function setLeadCommitteeShortName(?string $leadCommitteeShortName): static
    {
        $this->leadCommitteeShortName = $leadCommitteeShortName;
        return $this;
    }

    public function getLeadCommitteeId(): ?string
    {
        return $this->leadCommitteeId;
    }

    public function setLeadCommitteeId(?string $leadCommitteeId): static
    {
        $this->leadCommitteeId = $leadCommitteeId;
        return $this;
    }

    public function getRequestDate(): ?\DateTimeImmutable
    {
        return $this->requestDate;
    }

    public function setRequestDate(?\DateTimeImmutable $requestDate): static
    {
        $this->requestDate = $requestDate;
        return $this;
    }

    public function getFirstExtensionRequestDate(): ?\DateTimeImmutable
    {
        return $this->firstExtensionRequestDate;
    }

    public function setFirstExtensionRequestDate(?\DateTimeImmutable $firstExtensionRequestDate): static
    {
        $this->firstExtensionRequestDate = $firstExtensionRequestDate;
        return $this;
    }

    public function getFirstExtensionNoticeDate(): ?\DateTimeImmutable
    {
        return $this->firstExtensionNoticeDate;
    }

    public function setFirstExtensionNoticeDate(?\DateTimeImmutable $firstExtensionNoticeDate): static
    {
        $this->firstExtensionNoticeDate = $firstExtensionNoticeDate;
        return $this;
    }

    public function getSecondExtensionRequestDate(): ?\DateTimeImmutable
    {
        return $this->secondExtensionRequestDate;
    }

    public function setSecondExtensionRequestDate(?\DateTimeImmutable $secondExtensionRequestDate): static
    {
        $this->secondExtensionRequestDate = $secondExtensionRequestDate;
        return $this;
    }

    public function getSecondExtensionNoticeDate(): ?\DateTimeImmutable
    {
        return $this->secondExtensionNoticeDate;
    }

    public function setSecondExtensionNoticeDate(?\DateTimeImmutable $secondExtensionNoticeDate): static
    {
        $this->secondExtensionNoticeDate = $secondExtensionNoticeDate;
        return $this;
    }

    public function getExpiryDate(): ?\DateTimeImmutable
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(?\DateTimeImmutable $expiryDate): static
    {
        $this->expiryDate = $expiryDate;
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
