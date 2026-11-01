<?php

declare(strict_types=1);

namespace App\Tests\Unit\Filter;

use App\Api\Odata\Filter\DecisionFilter;
use PHPUnit\Framework\TestCase;

final class DecisionFilterTest extends TestCase
{
    private const GUID = '46e675de-8b56-4a21-9f86-000002005e1b';

    public function testEndpointIsBesluit(): void
    {
        self::assertSame('Besluit', new DecisionFilter()->endpoint());
    }

    public function testAgendaItemIdIsUnquoted(): void
    {
        $filter = new DecisionFilter(agendaItemId: self::GUID)->toQueryParams()['$filter'];

        self::assertSame(sprintf('Verwijderd eq false and Agendapunt_Id eq %s', self::GUID), $filter);
    }

    public function testAgendaItemIdsAreRenderedUnquoted(): void
    {
        $filter = new DecisionFilter(agendaItemIds: ['a-1', 'a-2'])->toQueryParams()['$filter'];

        self::assertSame('Verwijderd eq false and Agendapunt_Id in (a-1,a-2)', $filter);
    }

    public function testNoAgendaItemAddsNoCondition(): void
    {
        self::assertSame('Verwijderd eq false', new DecisionFilter()->toQueryParams()['$filter']);
    }

    public function testIncludeDeletedDropsTheCondition(): void
    {
        self::assertArrayNotHasKey('$filter', new DecisionFilter(includeDeleted: true)->toQueryParams());
    }

    public function testOrderTopAndSkipFlag(): void
    {
        $filter = new DecisionFilter(top: 10, withSkip: true);
        $params = $filter->toQueryParams();

        self::assertSame(10, $params['$top']);
        self::assertSame('GewijzigdOp desc', $params['$orderby']);
        self::assertTrue($filter->withSkip());
    }
}
