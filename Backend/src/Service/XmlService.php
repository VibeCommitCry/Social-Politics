<?php

namespace App\Service;

use Generator;
use SimpleXMLElement;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class XmlService
{
    private const NS_ATOM = 'http://www.w3.org/2005/Atom';
    private const NS_TK = 'http://www.tweedekamer.nl/xsd/tkData/v1-0';

    public function __construct(
        private HttpClientInterface $http,
        private string $governmentApiSyncfeedUrl,
    ) {
    }

    public function initialUrl(string $category): string
    {
        return rtrim($this->governmentApiSyncfeedUrl, '/') . "/Feed?category={$category}&content=internal";
    }

    /**
     * @return Generator<SimpleXMLElement>
     */
    public function fetchAll(string $url, callable $onCursor): Generator
    {
        do {
            $xml = new SimpleXMLElement($this->http->request('GET', $url)->getContent());
            $xml->registerXPathNamespace('atom', self::NS_ATOM);
            $xml->registerXPathNamespace('tk', self::NS_TK);

            $entries = $xml->xpath('//atom:entry');

            foreach ($entries as $entry) {
                $entry->registerXPathNamespace('atom', self::NS_ATOM);
                $entry->registerXPathNamespace('tk', self::NS_TK);

                $node = $entry->xpath('.//tk:*')[0] ?? null;
                if ($node !== null) {
                    yield $node;
                }
            }

            $nextUrl = $this->extractNextUrl($entries);

            if ($nextUrl !== null) {
                $onCursor($nextUrl);
            }

            $url = $nextUrl;
        } while ($url !== null && \count($entries) > 0);
    }

    /**
     * Resolves the resume URL for the sync cursor.
     *
     * IMPORTANT: the feed itself only carries a <link rel="next"> at the
     * top level once a page hits the 250-entity cap — categories smaller
     * than that (e.g. Fractie) never trigger it, so relying on the
     * feed-level link means the cursor is silently never saved for them.
     *
     * Instead, each individual <entry> carries its OWN <link rel="next">,
     * pointing to the feed page that starts at the following entity. This
     * exists regardless of category size, so we take the value from the
     * LAST entry processed on this page as the real resume point.
     */
    private function extractNextUrl(array $entries): ?string
    {
        if (empty($entries)) {
            return null;
        }

        $lastEntry = $entries[array_key_last($entries)];
        $lastEntry->registerXPathNamespace('atom', self::NS_ATOM);

        $links = $lastEntry->xpath("atom:link[@rel='next']/@href");

        return $links ? (string) $links[0] : null;
    }

    
}