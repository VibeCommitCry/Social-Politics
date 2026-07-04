<?php
// src/Command/TestDocumentApiCommand.php

namespace App\Command;

use App\Api\Dto\DocumentDto;
use App\Api\Filter\DocumentFilter;
use App\Api\Interface\ApiInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:api:document',
    description: 'Calls the real Tweede Kamer Document API and prints results',
)]
class TestDocumentApiCommand extends Command
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
            ->addOption('room', null, InputOption::VALUE_OPTIONAL, 'Filter by Kamer')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Filter by Soort');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filter = new DocumentFilter(
            top: (int) $input->getOption('top'),
            room: $input->getOption('room'),
            type: $input->getOption('type'),
        );

        $io->writeln(sprintf('Calling API with filter: %s', json_encode($filter->toQueryParams())));

        try {
            $results = $this->apiProvider->search($filter, DocumentDto::class );
        } catch (\Throwable $e) {
            $io->error(sprintf('API call failed: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        if (empty($results)) {
            $io->warning('No documents returned.');

            return Command::SUCCESS;
        }

        $rows = array_map(
            static fn($dto) => [
                $dto->id,
                $dto->titel ?? '(no title)',
                $dto->room,
                $dto->changedOn->format('Y-m-d H:i'),
                $dto->deleted ? 'yes' : 'no',
            ],
            $results
        );

        $io->table(['Id', 'Titel', 'Kamer', 'Changed On', 'Deleted'], $rows);
        $io->success(sprintf('%d document(s) returned.', count($results)));

        return Command::SUCCESS;
    }
}