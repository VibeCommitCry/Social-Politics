<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Dto\DecisionDto;
use App\Entity\Decision;
use App\Enum\DecisionStatus;
use App\Repository\DecisionRepository;
use App\Tests\Support\DatabaseTestCase;

final class DecisionRepositoryTest extends DatabaseTestCase
{
    private function repo(): DecisionRepository
    {
        $repo = $this->repository(Decision::class);
        \assert($repo instanceof DecisionRepository);

        return $repo;
    }

    private function save(string $id, string $modifiedOn, ?string $text = null): void
    {
        $this->repo()->saveDtoAsEntity(new DecisionDto(
            id: $id,
            agendaItemId: self::AGENDA_ITEM_ID,
            text: $text,
            status: DecisionStatus::Decision,
            modifiedOn: new \DateTimeImmutable($modifiedOn),
        ));
    }

    public function testNewIdInserts(): void
    {
        $this->save(self::DECISION_ID, '2026-01-01', 'Aangenomen');

        $decision = $this->em->find(Decision::class, self::DECISION_ID);

        self::assertNotNull($decision);
        self::assertSame('Aangenomen', $decision->getText());
        self::assertSame(DecisionStatus::Decision, $decision->getStatus());
        self::assertSame(self::AGENDA_ITEM_ID, $decision->getAgendaItemId());
    }

    public function testExistingIdUpdatesInPlace(): void
    {
        $this->save(self::DECISION_ID, '2026-01-01', 'Eerste');
        $this->save(self::DECISION_ID, '2026-02-01', 'Tweede');

        self::assertSame(1, $this->repo()->count([]));
        self::assertSame('Tweede', $this->em->find(Decision::class, self::DECISION_ID)?->getText());
    }

    public function testFindIdsOrdersByModifiedOnDescending(): void
    {
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', '2026-01-01');
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', '2026-03-01');
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3', '2026-02-01');

        self::assertSame(
            [
                'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2',
                'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3',
                'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1',
            ],
            $this->repo()->findIds(),
        );
    }

    public function testFindIdsRespectsLimit(): void
    {
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', '2026-01-01');
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', '2026-03-01');
        $this->save('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3', '2026-02-01');

        self::assertSame(
            ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3'],
            $this->repo()->findIds(2),
        );
    }

    public function testDeletedDefaultsToFalse(): void
    {
        $this->save(self::DECISION_ID, '2026-01-01');

        self::assertFalse($this->em->find(Decision::class, self::DECISION_ID)?->isDeleted());
    }
}
