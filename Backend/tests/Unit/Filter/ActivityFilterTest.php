<?php

declare(strict_types=1);

namespace App\Tests\Unit\Filter;

use App\Api\Odata\Filter\ActivityFilter;
use PHPUnit\Framework\TestCase;

final class ActivityFilterTest extends TestCase
{
    public function testEndpointIsActiviteit(): void
    {
        self::assertSame('Activiteit', new ActivityFilter()->endpoint());
    }

    public function testDefaultsExcludeDeletedAndOrderById(): void
    {
        $params = new ActivityFilter()->toQueryParams();

        self::assertSame('Verwijderd eq false', $params['$filter']);
        self::assertSame('Id asc', $params['$orderby']);
        self::assertSame(250, $params['$top']);
    }

    public function testIncludeDeletedDropsTheFilterEntirely(): void
    {
        self::assertArrayNotHasKey('$filter', new ActivityFilter(includeDeleted: true)->toQueryParams());
    }

    public function testRoomIsAQuotedString(): void
    {
        $filter = new ActivityFilter(room: 'Tweede Kamer')->toQueryParams()['$filter'];

        self::assertSame("Verwijderd eq false and Kamer eq 'Tweede Kamer'", $filter);
    }

    public function testQuoteInRoomIsEscaped(): void
    {
        $filter = new ActivityFilter(room: "Kamer's")->toQueryParams()['$filter'];

        self::assertStringContainsString("Kamer eq 'Kamer''s'", (string) $filter);
    }

    public function testIdsAreRenderedUnquoted(): void
    {
        $filter = new ActivityFilter(ids: ['a-1', 'a-2'])->toQueryParams()['$filter'];

        self::assertSame('Verwijderd eq false and Id in (a-1,a-2)', $filter);
    }

    public function testWithSkipReflectsConstructorFlag(): void
    {
        self::assertFalse(new ActivityFilter()->withSkip());
        self::assertTrue(new ActivityFilter(withSkip: true)->withSkip());
    }
}
