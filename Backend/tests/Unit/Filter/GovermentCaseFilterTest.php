<?php

declare(strict_types=1);

namespace App\Tests\Unit\Filter;

use App\Api\Odata\Filter\GovermentCaseFilter;
use PHPUnit\Framework\TestCase;

final class GovermentCaseFilterTest extends TestCase
{
    public function testEndpointIsZaak(): void
    {
        self::assertSame('Zaak', new GovermentCaseFilter()->endpoint());
    }

    public function testRoomAndTypeAreQuotedStrings(): void
    {
        $filter = new GovermentCaseFilter(type: 'Motie', room: 'Tweede Kamer')->toQueryParams()['$filter'];

        self::assertSame("Verwijderd eq false and Soort eq 'Motie' and Kamer eq 'Tweede Kamer'", $filter);
    }

    public function testCompletedRendersLowercaseBoolean(): void
    {
        self::assertStringContainsString('Afgedaan eq true', (string) new GovermentCaseFilter(completed: true)->toQueryParams()['$filter']);
        self::assertStringContainsString('Afgedaan eq false', (string) new GovermentCaseFilter(completed: false)->toQueryParams()['$filter']);
    }

    public function testExpandDecisionsAddsExpandParam(): void
    {
        self::assertSame('Besluit', new GovermentCaseFilter(expandDecisions: true)->toQueryParams()['$expand']);
        self::assertArrayNotHasKey('$expand', new GovermentCaseFilter()->toQueryParams());
    }

    public function testOrderByCanBeDisabled(): void
    {
        self::assertSame('Datum desc', new GovermentCaseFilter()->toQueryParams()['$orderby']);
        self::assertArrayNotHasKey('$orderby', new GovermentCaseFilter(orderBy: null)->toQueryParams());
    }
}
