<?php

namespace App\Api\Provider;

use App\Api\Dto\DocumentDto;
use App\Api\Filter\DocumentFilter;
use App\Api\Interface\DocumentInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class DocumentProvider implements DocumentInterface
{
    private const ENDPOINT = 'Document';

    public function __construct(
        private HttpClientInterface $govermentApiClient,
    ) {
    }

    public function search(DocumentFilter $filter): array
    {
        try {
            $response = $this->govermentApiClient->request('GET', self::ENDPOINT, [
                'query' => $filter->toQueryParams(),
            ]);

            $data = $response->toArray();
            $result = [];

            foreach ($data['value'] as $value) {
                array_push($result, DocumentDto::fromArray($value));
            }

            return $result;

        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException('Weather API request failed', previous: $e);
        }
    }
}