<?php

namespace App\Entity;

use App\Repository\GovermentCaseDecisionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GovermentCaseDecisionRepository::class)]
class GovermentCaseDecision
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: GovermentCase::class)]
    #[ORM\JoinColumn(name: 'goverment_case_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private GovermentCase $govermentCase;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Decision::class)]
    #[ORM\JoinColumn(name: 'decision_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Decision $decision;

    public function __construct(GovermentCase $govermentCase, Decision $decision)
    {
        $this->govermentCase = $govermentCase;
        $this->decision = $decision;
    }

    public function getGovermentCase(): GovermentCase
    {
        return $this->govermentCase;
    }

    public function getDecision(): Decision
    {
        return $this->decision;
    }
}