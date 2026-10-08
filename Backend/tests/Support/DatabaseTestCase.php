<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Api\Odata\Provider\ApiProvider;
use App\Import\ActivityImporter;
use App\Import\AgendaPointImporter;
use App\Import\DecisionImporter;
use App\Import\FractionImporter;
use App\Import\GovermentCaseImporter;
use App\Import\VoteImporter;
use App\Repository\ActivityRepository;
use App\Repository\AgendaPointRepository;
use App\Repository\DecisionRepository;
use App\Repository\FractionRepository;
use App\Repository\GovermentCaseDecisionRepository;
use App\Repository\GovermentCaseRepository;
use App\Repository\VoteRepository;
use App\Service\ImporterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Boots the kernel against the (empty) test DB and empties every table before each test,
 * so tests can seed exactly the rows they assert on.
 */
abstract class DatabaseTestCase extends KernelTestCase
{
    protected const FRACTION_ID = '11111111-1111-4111-8111-111111111111';
    protected const DECISION_ID = '22222222-2222-4222-8222-222222222222';
    protected const AGENDA_ITEM_ID = '33333333-3333-4333-8333-333333333333';
    protected const CASE_ID = '44444444-4444-4444-8444-444444444444';
    protected const ACTIVITY_ID = '55555555-5555-4555-8555-555555555555';

    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->truncateAllTables();
    }

    protected function tearDown(): void
    {
        $this->em->close();

        parent::tearDown();
    }

    /**
     * @param class-string $class
     */
    protected function repository(string $class): object
    {
        return $this->em->getRepository($class);
    }

    /**
     * Real ApiProvider over a MockHttpClient. Every outgoing request's query is recorded in $requests.
     *
     * @param list<MockResponse|list<array<string, mixed>>> $responses one entry per page: a MockResponse, or the OData `value` rows
     * @param list<array<string, string>>             $requests
     */
    protected function apiProvider(array $responses, array &$requests = []): ApiProvider
    {
        $queue = array_map(
            static fn(MockResponse|array $response): MockResponse => $response instanceof MockResponse
                ? $response
                : new MockResponse(json_encode(['value' => $response], JSON_THROW_ON_ERROR)),
            $responses,
        );

        $client = new MockHttpClient(
            static function (string $method, string $url) use (&$queue, &$requests): MockResponse {
                parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
                $requests[] = $query;

                return array_shift($queue) ?? new MockResponse(json_encode(['value' => []], JSON_THROW_ON_ERROR));
            },
            'https://odata.test/OData/v5/',
        );

        return new ApiProvider($client);
    }

    protected function importerService(): ImporterService
    {
        $decisions = $this->repository(\App\Entity\Decision::class);
        \assert($decisions instanceof DecisionRepository);
        $caseDecisions = $this->repository(\App\Entity\GovermentCaseDecision::class);
        \assert($caseDecisions instanceof GovermentCaseDecisionRepository);
        $cases = $this->repository(\App\Entity\GovermentCase::class);
        \assert($cases instanceof GovermentCaseRepository);
        $fractions = $this->repository(\App\Entity\Fraction::class);
        \assert($fractions instanceof FractionRepository);
        $votes = $this->repository(\App\Entity\Vote::class);
        \assert($votes instanceof VoteRepository);

        $activities = $this->repository(\App\Entity\Activity::class);
        \assert($activities instanceof ActivityRepository);
        $agendaPoints = $this->repository(\App\Entity\AgendaPoint::class);
        \assert($agendaPoints instanceof AgendaPointRepository);

        return new ImporterService([
            new ActivityImporter($activities),
            new AgendaPointImporter($agendaPoints),
            new DecisionImporter($decisions, $caseDecisions),
            new GovermentCaseImporter($cases),
            new FractionImporter($fractions),
            new VoteImporter($votes),
        ]);
    }

    private function truncateAllTables(): void
    {
        $connection = $this->em->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($connection->createSchemaManager()->listTableNames() as $table) {
            if ($table !== 'doctrine_migration_versions') {
                $connection->executeStatement(sprintf('TRUNCATE TABLE `%s`', $table));
            }
        }

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
