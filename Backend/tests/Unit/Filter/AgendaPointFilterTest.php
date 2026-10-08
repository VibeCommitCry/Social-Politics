<?php

declare(strict_types=1);

namespace App\Tests\Unit\Filter;

use App\Api\Odata\Filter\AgendaPointFilter;
use PHPUnit\Framework\TestCase;

final class AgendaPointFilterTest extends TestCase
{
    public function testEndpointIsAgendapunt(): void
    {
        self::assertSame('Agendapunt', new AgendaPointFilter()->endpoint());
    }

    public function testDefaultsExcludeDeletedAndOrderById(): void
    {
        $params = new AgendaPointFilter()->toQueryParams();

        self::assertSame('Verwijderd eq false', $params['$filter']);
        self::assertSame('Id asc', $params['$orderby']);
        self::assertSame(250, $params['$top']);
    }

    public function testIncludeDeletedDropsTheFilterEntirely(): void
    {
        self::assertArrayNotHasKey('$filter', new AgendaPointFilter(includeDeleted: true)->toQueryParams());
    }

    public function testRoomFiltersOnTheRelatedActivity(): void
    {
        $filter = new AgendaPointFilter(room: 'Tweede Kamer')->toQueryParams()['$filter'];

        self::assertSame("Verwijderd eq false and Activiteit/Kamer eq 'Tweede Kamer'", $filter);
    }

    public function testQuoteInRoomIsEscaped(): void
    {
        $filter = new AgendaPointFilter(room: "Kamer's")->toQueryParams()['$filter'];

        self::assertStringContainsString("Activiteit/Kamer eq 'Kamer''s'", (string) $filter);
    }

    public function testIdsAreRenderedUnquoted(): void
    {
        $filter = new AgendaPointFilter(ids: ['a-1', 'a-2'])->toQueryParams()['$filter'];

        self::assertSame('Verwijderd eq false and Id in (a-1,a-2)', $filter);
    }

    public function testWithSkipReflectsConstructorFlag(): void
    {
        self::assertFalse(new AgendaPointFilter()->withSkip());
        self::assertTrue(new AgendaPointFilter(withSkip: true)->withSkip());
    }
}
