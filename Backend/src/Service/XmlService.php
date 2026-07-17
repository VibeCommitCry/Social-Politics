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
        private string $governmentApiBaseUrl,
    ) {
    }

    public function initialUrl(string $category): string
    {
        return rtrim($this->governmentApiBaseUrl, '/') . "/Feed?category={$category}";
    }

    /**
     * Fetches every page for a category, yielding each entity's <tk:...> node.
     * Calls $onCursor(string $nextUrl) after each page so the caller can persist it.
     *
     * @return Generator<SimpleXMLElement>
     */
    public function fetchAll(string $url, callable $onCursor): Generator
    {
        do {
            $xml = new SimpleXMLElement($this->http->request(
                'GET',
                $url,
                [
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                        'Accept' => 'application/atom+xml, text/xml, */*',
                    ],
                ]
            )->getContent());
            $xml->registerXPathNamespace('atom', self::NS_ATOM);
            $xml->registerXPathNamespace('tk', self::NS_TK);

            $entries = $xml->xpath('//atom:entry');

            foreach ($entries as $entry) {
                $entry->registerXPathNamespace('tk', self::NS_TK);
                $node = $entry->xpath('.//tk:*')[0] ?? null; // works for any category
                if ($node !== null) {
                    yield $node;
                }
            }

            $nextLinks = $xml->xpath("atom:link[@rel='next']/@href");
            $url = $nextLinks ? (string) $nextLinks[0] : null;

            if ($url !== null) {
                $onCursor($url);
            }
        } while ($url !== null && \count($entries) > 0);
    }


}