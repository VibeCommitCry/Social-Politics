<?php

namespace App\Command;

use App\Api\SyncFeed\Provider\XmlProvider;
use App\Enum\CategoryEnum;
use App\Repository\FractionRepository;
use App\Repository\GovermentCaseRepository;
use App\Repository\SyncCursorRepository;
use App\Service\FractionService;
use App\Service\GovermentCaseService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sync:govermentcase',
    description: 'Sync Zaak (goverment case) data from the Tweede Kamer',
)]
class SyncGovermentCaseCommand extends Command
{
    private const Category = CategoryEnum::GOVERMENTCASE->value;

    public function __construct(
        private SyncCursorRepository $cursorRepo,
        private XmlProvider $xmlProvider,
        private GovermentCaseService $govermentCaseService,
        private GovermentCaseRepository $govermentCaseRepo,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cursor = $this->cursorRepo->find(self::Category);
        $url = $cursor?->getNextUrl() ?? $this->xmlProvider->initialUrl(self::Category);

        try {
            foreach ($this->xmlProvider->search($url, fn(string $next) => $this->cursorRepo->save(self::Category, $next)) as $node) {
                $this->upsertFractie($node);
            }

            $output->writeln('Goverment Case sync done.');

        } catch (\Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface $e) {
            $response = $e->getResponse();
            dump($response->getStatusCode());
            dump($response->getHeaders(false));
            dump($response->getContent(false));
            $output->writeln('Goverment Case sync failed.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function upsertFractie(\SimpleXMLElement $node): void
    {
        $data = $this->govermentCaseService->extractGovermentCaseData($node);
        $this->govermentCaseRepo->createGovermentCaseFromArray($data);
    }
}