<?php

declare(strict_types=1);

namespace App\Tests\Unit\Api;

use App\Api\Odata\Filter\DecisionFilter;
use App\Api\Odata\Provider\ApiProvider;
use App\Dto\DecisionDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

final class ApiProviderTest extends TestCase
{
    /**
     * @param list<MockResponse>          $responses
     * @param list<array<string, string>> $requests
     */
    private function provider(array $responses, array &$requests = []): ApiProvider
    {
        $client = new MockHttpClient(
            static function (string $method, string $url) use (&$responses, &$requests): MockResponse {
                parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
                $requests[] = ['path' => (string) parse_url($url, PHP_URL_PATH)] + $query;

                return array_shift($responses) ?? new MockResponse('{"value":[]}');
            },
            'https://odata.test/OData/v5/',
        );

        return new ApiProvider($client);
    }

    /**
     * @param list<string> $ids
     */
    private static function page(array $ids): MockResponse
    {
        return new MockResponse(json_encode(['value' => array_map(static fn(string $id) => ['Id' => $id], $ids)], JSON_THROW_ON_ERROR));
    }

    public function testPaginatesWithIncrementingSkipUntilAShortPage(): void
    {
        $requests = [];
        $provider = $this->provider([self::page(['a', 'b']), self::page(['c', 'd']), self::page(['e'])], $requests);

        $dtos = iterator_to_array($provider->search(new DecisionFilter(top: 2, withSkip: true), DecisionDto::class), false);

        self::assertSame(['a', 'b', 'c', 'd', 'e'], array_map(static fn(object $d) => $d instanceof DecisionDto ? $d->id : null, $dtos));
        self::assertCount(3, $requests);
        self::assertSame(['0', '2', '4'], array_column($requests, '$skip'));
        self::assertSame('/OData/v5/Besluit', $requests[0]['path']);
    }

    public function testFullPageFollowedByEmptyPageStops(): void
    {
        $requests = [];
        $provider = $this->provider([self::page(['a', 'b'])], $requests);

        $dtos = iterator_to_array($provider->search(new DecisionFilter(top: 2, withSkip: true), DecisionDto::class), false);

        self::assertCount(2, $dtos);
        self::assertCount(2, $requests);
    }

    public function testDoesNotPaginateWhenWithSkipIsFalse(): void
    {
        $requests = [];
        $provider = $this->provider([self::page(['a', 'b']), self::page(['c'])], $requests);

        $dtos = iterator_to_array($provider->search(new DecisionFilter(top: 2), DecisionDto::class), false);

        self::assertCount(2, $dtos);
        self::assertCount(1, $requests);
        self::assertArrayNotHasKey('$skip', $requests[0]);
    }

    public function testSendsTheFilterQueryParams(): void
    {
        $requests = [];
        $provider = $this->provider([self::page([])], $requests);

        iterator_to_array($provider->search(new DecisionFilter(top: 7), DecisionDto::class));

        self::assertSame('7', $requests[0]['$top']);
        self::assertSame('Verwijderd eq false', $requests[0]['$filter']);
        self::assertSame('GewijzigdOp desc', $requests[0]['$orderby']);
    }

    public function testTransportErrorIsWrappedInRuntimeException(): void
    {
        $provider = $this->provider([new MockResponse('', ['error' => 'connection refused'])]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API request failed');

        iterator_to_array($provider->search(new DecisionFilter(), DecisionDto::class));
    }

    public function testHttpErrorStatusSurfacesAsAnExceptionNotAnEmptyResult(): void
    {
        // Pins current behaviour: 4xx/5xx is not wrapped, but it must never look like "no results".
        $provider = $this->provider([new MockResponse('{"error":"bad filter"}', ['http_code' => 400])]);

        $this->expectException(ClientExceptionInterface::class);

        iterator_to_array($provider->search(new DecisionFilter(), DecisionDto::class));
    }
}
