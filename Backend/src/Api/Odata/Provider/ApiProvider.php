<?php

namespace App\Api\Odata\Provider;

use App\Interface\FilterInterface;
use App\Interface\OdataInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class ApiProvider implements OdataInterface
{
    public function __construct(
        private HttpClientInterface $governmentApiOdataUrl,
    ) {
    }

    /**
     * @return \Generator<object>
     */
    public function search(FilterInterface $filter, string $dto): \Generator
    {
        $baseQuery = $filter->toQueryParams();
        $top = $baseQuery['$top'] ?? 250;
        $skip = 0;

        do {
            $query = array_merge($baseQuery, ['$top' => $top, '$skip' => $skip]);

            try {
                $response = $this->governmentApiOdataUrl->request('GET', $filter->endpoint(), ['query' => $query]);
                $data = $response->toArray();
            } catch (TransportExceptionInterface $e) {
                throw new \RuntimeException('API request failed', previous: $e);
            }

            $count = count($data['value']);
            foreach ($data['value'] as $value) {
                yield $dto::fromArray($value);
            }

            $skip += $top;
        } while ($count === $top);
    }


}