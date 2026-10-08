<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Dto\DecisionDto;
use App\Dto\FractionDto;
use App\Dto\VoteDto;
use App\Entity\Decision;
use App\Entity\Fraction;
use App\Entity\Vote;
use App\Enum\VoteType;
use App\Repository\DecisionRepository;
use App\Repository\FractionRepository;
use App\Repository\VoteRepository;
use App\Tests\Support\DatabaseTestCase;

final class VoteRepositoryTest extends DatabaseTestCase
{
    private const VOTE_ID = '55555555-5555-4555-8555-555555555555';

    private function repo(): VoteRepository
    {
        $repo = $this->repository(Vote::class);
        \assert($repo instanceof VoteRepository);

        return $repo;
    }

    private function seedParents(): void
    {
        $decisions = $this->repository(Decision::class);
        \assert($decisions instanceof DecisionRepository);
        $decisions->saveDtoAsEntity(new DecisionDto(id: self::DECISION_ID, agendaItemId: self::AGENDA_ITEM_ID));

        $fractions = $this->repository(Fraction::class);
        \assert($fractions instanceof FractionRepository);
        $fractions->saveDtoAsEntity(new FractionDto(id: self::FRACTION_ID, abbreviation: 'VVD', seatCount: 24));
    }

    public function testSavesVoteWithResolvedRelations(): void
    {
        $this->seedParents();

        $this->repo()->saveDtoAsEntity(new VoteDto(
            id: self::VOTE_ID,
            decisionId: self::DECISION_ID,
            type: VoteType::For,
            fractionId: self::FRACTION_ID,
        ));

        $vote = $this->em->find(Vote::class, self::VOTE_ID);

        self::assertNotNull($vote);
        self::assertSame(self::DECISION_ID, $vote->getDecisionId());
        self::assertSame(self::FRACTION_ID, $vote->getFractionId());
        self::assertSame(VoteType::For, $vote->getType());
    }

    public function testFractionSnapshotsAreStoredAsGivenNotDerivedFromTheFraction(): void
    {
        $this->seedParents();

        // The Fraction row says VVD with 24 seats; the vote was cast when it was "OldName" with 31.
        $this->repo()->saveDtoAsEntity(new VoteDto(
            id: self::VOTE_ID,
            decisionId: self::DECISION_ID,
            fractionId: self::FRACTION_ID,
            fractionSize: 31,
            actorFraction: 'OldName',
        ));

        $vote = $this->em->find(Vote::class, self::VOTE_ID);

        self::assertNotNull($vote);
        self::assertSame(31, $vote->getFractionSize());
        self::assertSame('OldName', $vote->getFractionName());
    }

    public function testFractionIsOptional(): void
    {
        $this->seedParents();

        $this->repo()->saveDtoAsEntity(new VoteDto(id: self::VOTE_ID, decisionId: self::DECISION_ID));

        $vote = $this->em->find(Vote::class, self::VOTE_ID);

        self::assertNotNull($vote);
        self::assertNull($vote->getFraction());
        self::assertNull($vote->getFractionId());
    }

    public function testExistingIdUpdatesInPlace(): void
    {
        $this->seedParents();

        $this->repo()->saveDtoAsEntity(new VoteDto(id: self::VOTE_ID, decisionId: self::DECISION_ID, type: VoteType::For));
        $this->repo()->saveDtoAsEntity(new VoteDto(id: self::VOTE_ID, decisionId: self::DECISION_ID, type: VoteType::Against));

        self::assertSame(1, $this->repo()->count([]));
        self::assertSame(VoteType::Against, $this->em->find(Vote::class, self::VOTE_ID)?->getType());
    }

    public function testVoteWithoutDecisionIdIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repo()->saveDtoAsEntity(new VoteDto(id: self::VOTE_ID));
    }

    // A vote whose Decision row is missing is deliberately untested: whether it should throw at
    // flush (current getReference() behaviour) or fail an explicit check is still an open decision.
}
