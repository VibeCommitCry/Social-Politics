<?php

namespace App\Command;

use App\Entity\Fraction;
use App\Repository\FractionRepository;
use App\Repository\SyncCursorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:sync:fraction',
    description: 'Get the current Second room situation',
)]
class SyncFractieCommand extends Command
{
    private const NS_TK = 'http://www.tweedekamer.nl/xsd/tkData/v1-0';
    private const NS_ATOM = 'http://www.w3.org/2005/Atom';

    public function __construct(
        private HttpClientInterface $http,
        private EntityManagerInterface $em,
        private FractionRepository $fractieRepo,
        private SyncCursorRepository $cursorRepo,
        private string $governmentApiBaseUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cursor = $this->cursorRepo->find('Fractie');

        $initialUrl = rtrim($this->governmentApiBaseUrl, '/') . '/Feed?category=Fractie&content=internal';
        $url = $cursor?->getNextUrl() ?? $initialUrl;

        do {
            $xml = new \SimpleXMLElement($this->http->request('GET', $url)->getContent());
            $xml->registerXPathNamespace('atom', self::NS_ATOM);
            $xml->registerXPathNamespace('tk', self::NS_TK);

            foreach ($xml->xpath('//atom:entry') as $entry) {
                $this->upsertFractie($entry);
            }

            $nextLinks = $xml->xpath("atom:link[@rel='next']/@href");
            $url = $nextLinks ? (string) $nextLinks[0] : null;

            if ($url) {
                $this->cursorRepo->save('Fractie', $url);
            }
        } while ($url !== null && \count($xml->xpath('//atom:entry')) > 0);

        $this->em->flush();
        $output->writeln('Fractie sync done.');

        return Command::SUCCESS;
    }

      private function upsertFractie(\SimpleXMLElement $entry): void
    {
        $entry->registerXPathNamespace('tk', self::NS_TK);
        $node = $entry->xpath('.//tk:fractie')[0] ?? null;
        if (!$node) {
            return;
        }

        $id = (string) $node['id'];
        $fractie = $this->fractieRepo->find($id) ?? new Fraction($id);

        $fractie->setAbbreviation((string) $node->afkorting);
        $fractie->setNameNl((string) $node->naamNL);
        $fractie->setNameEn((string) $node->naamEN);
        $fractie->setSeatCount((int) $node->aantalZetels);
        $fractie->setDeleted(filter_var($node['verwijderd'] ?? 'false', FILTER_VALIDATE_BOOL));

        $this->em->persist($fractie);
    }
}
