<?php

namespace App\Command;

use App\Entity\Fraction;
use App\Repository\FractionRepository;
use App\Repository\SyncCursorRepository;
use App\Service\XmlService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:sync:fraction',
    description: 'Get the current Second room situation',
)]
final class SyncFractieCommand extends Command
{
    private const NS_TK = 'http://www.tweedekamer.nl/xsd/tkData/v1-0';
    private const NS_ATOM = 'http://www.w3.org/2005/Atom';

    public function __construct(
        private HttpClientInterface $http,
        private EntityManagerInterface $em,
        private FractionRepository $fractieRepo,
        private SyncCursorRepository $cursorRepo,
        private XmlService $xmlService,
        private string $governmentApiBaseUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cursor = $this->cursorRepo->find('Fractie');
        $url = $cursor?->getNextUrl() ?? $this->xmlService->initialUrl('Fractie');

        try {
            foreach ($this->xmlService->fetchAll($url, fn(string $next) => $this->cursorRepo->save('Fractie', $next)) as $node) {
                $this->upsertFractie($node);
            }

            $this->em->flush();
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
        $id = (string) $node['id'];
        $fractie = $this->fractieRepo->find($id) ?? new Fraction($id);

        $fractie->setAbbreviation((string) $node->afkorting);
        $fractie->setNameNl((string) $node->naamNL);
        $fractie->setNameEN((string) $node->naamEN);
        $fractie->setSeatCount((int) $node->aantalZetels);
        $fractie->setDeleted(filter_var($node['verwijderd'] ?? 'false', FILTER_VALIDATE_BOOL));

        $this->em->persist($fractie);
        $this->em->flush();
        $this->em->clear();
    }

}