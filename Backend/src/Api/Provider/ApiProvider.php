<?php

namespace App\Api\Provider;

use App\Api\Interface\ApiInterface;
use App\Api\Interface\FilterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class ApiProvider implements ApiInterface
{

    public function __construct(
        private HttpClientInterface $governmentApiOdataUrl,
    ) {
    }

    public function search(FilterInterface $filter, string $dto): array
    {
        try {

            $response = $this->governmentApiOdataUrl->request('GET',$filter->endpoint(), [
                'query' => $filter->toQueryParams(),
            ]);

            dump($response->getInfo('url')); 

            $data = $response->toArray();
            $result = [];

            foreach ($data['value'] as $value) {
                array_push($result, $dto::fromArray($value));
            }

            return $result;

        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException('API request failed', previous: $e);
        }
    }
}