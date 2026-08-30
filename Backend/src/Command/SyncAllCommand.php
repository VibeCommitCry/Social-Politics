<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:sync:all', description: 'Run all syncs in dependency order')]
class SyncAllCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->getApplication();

        $order = [
            'app:sync:case',      // pulls Zaak + linked Besluit
            'app:sync:decision',  // pulls remaining Besluit not attached to any Zaak
            'app:sync:vote',      // depends on Besluit existing
        ];

        foreach ($order as $name) {
            $output->writeln("<info>Running {$name}...</info>");
            $exitCode = $application->find($name)->run($input, $output);

            if ($exitCode !== Command::SUCCESS) {
                $output->writeln("<error>{$name} failed, aborting remaining syncs.</error>");
                return $exitCode;
            }
        }

        return Command::SUCCESS;
    }
}
