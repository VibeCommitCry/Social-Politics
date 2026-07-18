<?php

namespace App\Command;

use App\Api\Syncfeed\Service\XmlService;
use App\Enum\CategoryEnum;
use App\Repository\FractionRepository;
use App\Repository\SyncCursorRepository;
use App\Service\FractionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sync:fraction',
    description: 'Get the current Second room situation',
)]
class SyncFractieCommand extends Command
{
    private const Category = CategoryEnum::FRACTION->value;

    public function __construct(
        private SyncCursorRepository $cursorRepo,
        private XmlService $xmlService,
        private FractionService $fractionService,
        private FractionRepository $fractieRepo,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cursor = $this->cursorRepo->find(self::Category);
        $url = $cursor?->getNextUrl() ?? $this->xmlService->initialUrl(self::Category);

        try {
            foreach ($this->xmlService->fetchAll($url, fn(string $next) => $this->cursorRepo->save(self::Category, $next)) as $node) {
                $this->upsertFractie($node);
            }

            $output->writeln('Fractie sync done.');

        } catch (\Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface $e) {
            $response = $e->getResponse();
            dump($response->getStatusCode());
            dump($response->getHeaders(false));
            dump($response->getContent(false));
            $output->writeln('Fractie sync failed.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function upsertFractie(\SimpleXMLElement $node): void
    {
        $data = $this->fractionService->extractFractieData($node);
        $this->fractieRepo->createFractionFromArray($data);
    }
}