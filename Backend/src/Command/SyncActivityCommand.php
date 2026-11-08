<?php

declare(strict_types=1);

namespace App\Command;

use App\Api\Odata\Filter\ActivityFilter;
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

    public function __construct(
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
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'From this to to the present', null)
            ->addOption('skip', null, InputOption::VALUE_REQUIRED, 'Enable OData pagination offset skipping', true);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $sinceOption = $input->getOption('since');
        $since = is_string($sinceOption) && $sinceOption !== '' ? new DateTimeImmutable($sinceOption) : $this->since;

        $filter = new ActivityFilter(
            top: $this->intOption($input, 'top') ?? 250,
            withSkip: (bool) $input->getOption('skip'),
            room: 'Tweede Kamer',
            since: $since,
        );

        try {
            $count = $this->syncRunner->run($filter, $this->intOption($input, 'max') ?? 500, $output);
            $output->writeln("Activity sync done. {$count} total.");
        } catch (\Throwable $e) {
            $output->writeln('<error>Activity sync failed: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
