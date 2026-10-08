<?php

declare(strict_types=1);

namespace App\Tests\Unit\Filter;

use App\Api\Odata\Filter\FractionFilter;
use App\Enum\FractionField;
use PHPUnit\Framework\TestCase;

final class FractionFilterTest extends TestCase
{
    public function testEndpointIsFractie(): void
    {
        self::assertSame('Fractie', new FractionFilter()->endpoint());
    }

    public function testDefaultsExcludeDeletedAndNamelessFractions(): void
    {
        self::assertSame(
            'Verwijderd eq false and NaamNL ne null',
            new FractionFilter()->toQueryParams()['$filter'],
        );
    }

    public function testSyncStyleFilterKeepsNamelessAndOrdersById(): void
    {
        $params = new FractionFilter(
            top: 250,
            orderDirection: 'asc',
            orderBy: FractionField::Id,
            includeNamelessFraction: true,
            withSkip: true,
        )->toQueryParams();

        self::assertSame('Verwijderd eq false', $params['$filter']);
        self::assertSame('Id asc', $params['$orderby']);
    }

    public function testAbbreviationIsQuotedAndEscaped(): void
    {
        $filter = (string) new FractionFilter(includeNamelessFraction: true, searchByName: "D'66")->toQueryParams()['$filter'];

        self::assertStringContainsString("Afkorting eq 'D''66'", $filter);
    }
}
