<?php
// tests/Api/Provider/DocumentProviderTest.php

namespace App\Tests\Api\Provider;

use App\Api\Dto\DocumentDto;
use App\Api\Enum\DocumentField;
use App\Api\Filter\DocumentFilter;
use App\Api\Provider\ApiProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DocumentProviderTest extends TestCase
{
    public function testSearchReturnsMappedDtos(): void
    {
        $payload = [
            '@odata.context' => 'https://gegevensmagazijn.tweedekamer.nl/OData/v5/$metadata#Document',
            'value' => [
                [
                    DocumentField::Id->value => '792a2ac6-2346-45b8-8f86-011db4716bc2',
                    DocumentField::Type->value => 'Bijlage',
                    DocumentField::DocumentNumber->value => '2026D35449',
                    DocumentField::Titel->value => null,
                    DocumentField::Subject->value => 'Beslisnota bij antwoord op vragen van de leden De Roon en Wilders over het UNRWA-terror-network',
                    DocumentField::DiscussionYear->value => '2025-2026',
                    DocumentField::CiteTitel->value => null,
                    DocumentField::Alias->value => null,
                    DocumentField::Room->value => 'Tweede Kamer',
                    DocumentField::ContentType->value => 'application/pdf',
                    DocumentField::ContentLength->value => 466135,
                    DocumentField::ChangedOn->value => '2026-07-03T17:22:36.487+02:00',
                    DocumentField::ApiChangedOn->value => '2026-07-03T15:22:45.4047479Z',
                    DocumentField::Deleted->value => false,
                    'HuidigeDocumentVersie_Id' => 'f6daa6dc-5f71-49c8-a25a-433cf2d37412',
                ],
                [
                    DocumentField::Id->value => 'd39bb39b-ab5f-4d65-bf30-015426d6d0fb',
                    DocumentField::Type->value => 'Bijlage',
                    DocumentField::DocumentNumber->value => '2026D35329',
                    DocumentField::Titel->value => null,
                    DocumentField::Subject->value => 'Beslisnota bij Kamerbrief Voortgang beleids- en wetswijzigingen medicinale cannabis',
                    DocumentField::DiscussionYear->value => '2025-2026',
                    DocumentField::CiteTitel->value => null,
                    DocumentField::Alias->value => null,
                    DocumentField::Room->value => 'Tweede Kamer',
                    DocumentField::ContentType->value => 'application/pdf',
                    DocumentField::ContentLength->value => 1038757,
                    DocumentField::ChangedOn->value => '2026-07-03T15:09:07.453+02:00',
                    DocumentField::ApiChangedOn->value => '2026-07-03T13:09:26.0255114Z',
                    DocumentField::Deleted->value => false,
                    DocumentField::CurrentDocumentVersion_Id->value => 'f0b081f7-73e2-4144-89eb-8d5f004fa08b',
                ],
            ],
        ];

        $mockResponse = new MockResponse(json_encode($payload), [
            'response_headers' => ['content-type' => 'application/json'],
        ]);

        $client = new MockHttpClient([$mockResponse]);
        $provider = new ApiProvider($client);

        $results = $provider->search(new DocumentFilter(top: 5), DocumentDto::class);

        $this->assertCount(2, $results);
        $this->assertSame('792a2ac6-2346-45b8-8f86-011db4716bc2', $results[0]->id);
        $this->assertNull($results[0]->titel);
        $this->assertSame(466135, $results[0]->contentLength);
        $this->assertFalse($results[0]->deleted);
        $this->assertSame('d39bb39b-ab5f-4d65-bf30-015426d6d0fb', $results[1]->id);
    }
}