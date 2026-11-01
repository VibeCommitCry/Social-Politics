<?php

declare(strict_types=1);

namespace App\Command;

use App\Api\Odata\Filter\ActivityFilter;
use App\Dto\ActivityDto;
use App\Interface\OdataInterface;
use App\Repository\AgendaPointRepository;
use App\Service\ImporterService;
use App\Service\SyncRunner;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:sync:activity', description: 'Sync all Tweede Kamer Activiteit via OData')]
final class SyncActivityCommand extends Command
{
    use ReadsIntOptions;

    private const BATCH_SIZE = 50;
    private const DTOCLASS = ActivityDto::class;
    public function __construct(
        private OdataInterface $apiProvider,
        private ImporterService $importerService,
        private DateTimeImmutable $since,
        private SyncRunner $syncRunner
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('top', null, InputOption::VALUE_REQUIRED, 'Page size', 250)
            ->addOption('max', null, InputOption::VALUE_REQUIRED, 'Maximum total records to sync in this run', 500)
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'From this to to the present', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $sinceOption = $input->getOption('since');
        $since = $sinceOption !== null ? new DateTimeImmutable($sinceOption) : $this->since;

        $filter = new ActivityFilter(
            top: $this->intOption($input, 'top') ?? 250,
            withSkip: true,
            room: 'Tweede Kamer',
            since: $since,
        );

        try {
            $count = $this->syncRunner->run($filter, self::DTOCLASS, $this->intOption($input, 'max') ?? 500, $output);
            $output->writeln("Activity sync done. {$count} total.");
        } catch (\Throwable $e) {
            $output->writeln('<error>Activity sync failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}