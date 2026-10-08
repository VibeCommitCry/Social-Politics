<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\DecisionStatus;
use App\Enum\VoteType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EnumRoundTripTest extends TestCase
{
    /**
     * @return iterable<string, array{string, DecisionStatus}>
     */
    public static function decisionStatuses(): iterable
    {
        // The status strings come from a best-effort split of the API docs (see DecisionStatus).
        yield 'Besluit' => ['Besluit', DecisionStatus::Decision];
        yield 'Concept voorstel' => ['Concept voorstel', DecisionStatus::DraftProposal];
        yield 'Nog te verwerken besluit' => ['Nog te verwerken besluit', DecisionStatus::Pending];
        yield 'Voorstel' => ['Voorstel', DecisionStatus::Proposal];
    }

    #[DataProvider('decisionStatuses')]
    public function testDecisionStatusRoundTrip(string $value, DecisionStatus $expected): void
    {
        self::assertSame($expected, DecisionStatus::tryFrom($value));
        self::assertSame($value, $expected->value);
    }

    public function testDecisionStatusHasNoUntestedCases(): void
    {
        $tested = array_values(array_map(static fn(array $row): string => $row[0], iterator_to_array(self::decisionStatuses())));
        $actual = array_map(static fn(DecisionStatus $case): string => $case->value, DecisionStatus::cases());

        self::assertEqualsCanonicalizing($tested, $actual);
    }

    /**
     * @return iterable<string, array{string, VoteType}>
     */
    public static function voteTypes(): iterable
    {
        yield 'Voor' => ['Voor', VoteType::For];
        yield 'Tegen' => ['Tegen', VoteType::Against];
        yield 'Niet deelgenomen' => ['Niet deelgenomen', VoteType::Abstain];
    }

    #[DataProvider('voteTypes')]
    public function testVoteTypeRoundTrip(string $value, VoteType $expected): void
    {
        self::assertSame($expected, VoteType::tryFrom($value));
    }

    public function testVoteTypeHasNoUntestedCases(): void
    {
        $tested = array_values(array_map(static fn(array $row): string => $row[0], iterator_to_array(self::voteTypes())));
        $actual = array_map(static fn(VoteType $case): string => $case->value, VoteType::cases());

        self::assertEqualsCanonicalizing($tested, $actual);
    }

    #[DataProvider('unknownValues')]
    public function testUnknownValuesFallThroughToNull(string $value): void
    {
        self::assertNull(DecisionStatus::tryFrom($value));
        self::assertNull(VoteType::tryFrom($value));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unknownValues(): iterable
    {
        yield 'unknown word' => ['onbekend'];
        yield 'empty string' => [''];
        yield 'wrong case' => ['voor'];
    }
}
