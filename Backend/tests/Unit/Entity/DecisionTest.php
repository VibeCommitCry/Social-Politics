<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Activity;
use App\Entity\AgendaPoint;
use App\Entity\Decision;
use PHPUnit\Framework\TestCase;

final class DecisionTest extends TestCase
{
    private function decision(?AgendaPoint $agendaPoint): Decision
    {
        $decision = new Decision('d-1');
        $decision->setAgendaPoint($agendaPoint);

        return $decision;
    }

    public function testVotedOnIsNullWithoutAgendaPoint(): void
    {
        self::assertNull($this->decision(null)->getVotedOn());
    }

    public function testVotedOnIsNullWhenAgendaPointHasNoActivity(): void
    {
        self::assertNull($this->decision(new AgendaPoint('a-1'))->getVotedOn());
    }

    public function testVotedOnIsNullWhenActivityHasNoDate(): void
    {
        $agendaPoint = new AgendaPoint('a-1')->setActivity(new Activity('act-1'));

        self::assertNull($this->decision($agendaPoint)->getVotedOn());
    }

    public function testVotedOnIsTheActivityDayInAmsterdam(): void
    {
        $activity = new Activity('act-1')->setDate(new \DateTimeImmutable('2026-03-01 00:00:00'));
        $votedOn = $this->decision(new AgendaPoint('a-1')->setActivity($activity))->getVotedOn();

        self::assertSame('2026-03-01', $votedOn?->format('Y-m-d'));
        self::assertSame('Europe/Amsterdam', $votedOn->getTimezone()->getName());
    }

    public function testVotedOnKeepsTheWallClockDayWhenHydratedInAnotherTimeZone(): void
    {
        // Doctrine hydrates the Amsterdam wall-clock column in the PHP default zone, so the day must not shift.
        $activity = new Activity('act-1')->setDate(new \DateTimeImmutable('2026-03-01 00:00:00', new \DateTimeZone('UTC')));
        $votedOn = $this->decision(new AgendaPoint('a-1')->setActivity($activity))->getVotedOn();

        self::assertSame('2026-03-01', $votedOn?->format('Y-m-d'));
        self::assertSame('00:00:00', $votedOn->format('H:i:s'));
    }
}
