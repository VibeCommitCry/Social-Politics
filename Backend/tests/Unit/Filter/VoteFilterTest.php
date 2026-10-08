<?php

declare(strict_types=1);

namespace App\Tests\Unit\Filter;

use App\Api\Odata\Filter\VoteFilter;
use PHPUnit\Framework\TestCase;

final class VoteFilterTest extends TestCase
{
    private const GUID_A = '46e675de-8b56-4a21-9f86-000002005e1b';
    private const GUID_B = '56e675de-8b56-4a21-9f86-000002005e1b';

    public function testEndpointIsStemming(): void
    {
        self::assertSame('Stemming', new VoteFilter()->endpoint());
    }

    public function testDecisionIdsAreUnquotedInTheInClause(): void
    {
        // Edm.Guid values must not be quoted; the live API rejects quoted GUID literals on `eq`.
        $params = new VoteFilter(decisionIds: [self::GUID_A, self::GUID_B])->toQueryParams();

        self::assertStringContainsString(sprintf('Besluit_Id in (%s,%s)', self::GUID_A, self::GUID_B), (string) $params['$filter']);
        self::assertStringNotContainsString("'", (string) $params['$filter']);
    }

    public function testEmptyDecisionIdsAddNoInClause(): void
    {
        $params = new VoteFilter()->toQueryParams();

        self::assertStringNotContainsString(' in ', (string) $params['$filter']);
    }

    public function testDeletedRowsAreExcludedByDefault(): void
    {
        self::assertSame('Verwijderd eq false', new VoteFilter()->toQueryParams()['$filter']);
    }

    public function testIncludeDeletedDropsTheCondition(): void
    {
        $params = new VoteFilter(includeDeleted: true)->toQueryParams();

        self::assertArrayNotHasKey('$filter', $params);
    }

    public function testConditionsAreJoinedWithAnd(): void
    {
        $filter = new VoteFilter(decisionIds: [self::GUID_A])->toQueryParams()['$filter'];

        self::assertSame(sprintf('Verwijderd eq false and Besluit_Id in (%s)', self::GUID_A), $filter);
    }

    public function testOrderAndTopAndSkipFlag(): void
    {
        $filter = new VoteFilter(top: 50, withSkip: true);
        $params = $filter->toQueryParams();

        self::assertSame(50, $params['$top']);
        self::assertSame('GewijzigdOp desc', $params['$orderby']);
        self::assertTrue($filter->withSkip());
        self::assertFalse(new VoteFilter()->withSkip());
    }
}
