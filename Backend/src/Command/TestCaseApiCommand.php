<?php
// src/Command/TestCaseApiCommand.php

namespace App\Command;

use App\Api\Dto\CaseDto;
use App\Api\Filter\CaseFilter;
use App\Api\Interface\ApiInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:api:case',
    description: 'Calls the real Tweede Kamer Zaak API and prints results',
)]
class TestCaseApiCommand extends Command
{
    public function __construct(
        private ApiInterface $apiProvider,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('top', null, InputOption::VALUE_REQUIRED, 'Number of results', 5)
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Filter by Soort')
            ->addOption('room', null, InputOption::VALUE_OPTIONAL, 'Filter by Kamer', "Tweede Kamer");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filter = new CaseFilter(
            top: (int) $input->getOption('top'),
            type: $input->getOption('type'),
            room: $input->getOption('room'),
        );

        $io->writeln(sprintf('Calling API with filter: %s', json_encode($filter->toQueryParams())));

        try {
            $results = $this->apiProvider->search($filter, CaseDto::class);
        } catch (\Throwable $e) {
            $io->error(sprintf('API call failed: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        if (empty($results)) {
            $io->warning('No cases returned.');

            return Command::SUCCESS;
        }

        $rows = array_map(
            static fn(CaseDto $dto) => [
                $dto->id,
                $dto->type,
                $dto->room,
                $dto->date?->format('Y-m-d'),
                $dto->deleted ? 'yes' : 'no',
            ],
            $results
        );

        $io->table(['Id', 'Soort', 'Kamer', 'Datum', 'Deleted'], $rows);

        foreach ($results as $dto) {
            $io->writeln(sprintf(
                '<comment>%s</comment>: %s',
                $dto->id,
                $dto->subject ?? '(no subject)'
            ));
        }

        $io->success(sprintf('%d case(s) returned.', count($results)));

        return Command::SUCCESS;
    }
}