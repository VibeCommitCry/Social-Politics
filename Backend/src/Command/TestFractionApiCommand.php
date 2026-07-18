<?php
// src/Command/TestFractionApiCommand.php

namespace App\Command;

use App\Api\Odata\Dto\FractionDto;
use App\Api\Odata\Enum\FractionField;
use App\Api\Odata\Filter\FractionFilter;
use App\Api\Odata\Interface\ApiInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// get the current parties in the second room

#[AsCommand(
    name: 'app:api:fraction',
    description: 'Calls the real Tweede Kamer API and prints Fractie results',
)]
class TestFractionApiCommand extends Command
{
    public function __construct(
        private ApiInterface $apiProvider,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('top', null, InputOption::VALUE_REQUIRED, 'Number of results', 50)
            ->addOption('order-by', null, InputOption::VALUE_OPTIONAL, 'Field to order by (e.g. GewijzigdOp, NaamNL)', FractionField::SeatCount->value)
            ->addOption('order-direction', null, InputOption::VALUE_OPTIONAL, 'asc or desc', 'desc')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL)
            ->addOption('include-deleted', null, InputOption::VALUE_NONE, 'Include deleted fractions');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $orderByOption = $input->getOption('order-by');
        $orderBy = FractionField::tryFrom($orderByOption);

        if ($orderBy === null) {
            $io->error(sprintf(
                'Invalid --order-by value "%s". Valid options: %s',
                $orderByOption,
                implode(', ', array_column(FractionField::cases(), 'value')),
            ));

            return Command::FAILURE;
        }

        $filter = new FractionFilter(
            top: (int) $input->getOption('top'),
            orderDirection: $input->getOption('order-direction'),
            searchByName: $input->getOption('name'),
            orderBy: $orderBy,
            includeDeleted: (bool) $input->getOption('include-deleted'),
        );

        $io->writeln(sprintf('Calling API with filter: %s', json_encode($filter->toQueryParams())));

        try {
            $results = $this->apiProvider->search($filter, FractionDto::class);
        } catch (\Throwable $e) {
            $io->error(sprintf('API call failed: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        if (empty($results)) {
            $io->warning('No fractions returned.');

            return Command::SUCCESS;
        }

        $rows = array_map(
            static fn(FractionDto $dto) => [
                $dto->id,
                $dto->afkorting,
                $dto->naamNL,
                $dto->aantalZetels,
            ],
            $results
        );

        $io->table(['Id', 'Afkorting', 'NaamNL', 'Zetels'], $rows);
        $io->success(sprintf('%d fraction(s) returned.', count($results)));

        return Command::SUCCESS;
    }
}