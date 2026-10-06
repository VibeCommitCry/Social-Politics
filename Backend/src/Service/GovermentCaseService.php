<?php

declare(strict_types=1);

namespace App\Service;

class GovermentCaseService
{
    public function __construct(
    ) {}

    /**
     * Extracts case (Zaak) fields from a raw <tk:zaak> XML node into a plain array.
     *
     * @return array<string, string|bool|null>
     */
    public function extractGovermentCaseData(\SimpleXMLElement $node): array
    {
        return [
            'id' => (string) $node['id'],
            'number' => (string) $node->nummer,
            'type' => (string) $node->soort,
            'title' => (string) $node->titel,
            'subject' => (string) $node->onderwerp,
            'startedOn' => (string) $node->gestartOp ?: null,
            'organization' => (string) $node->organisatie,
            'currentStatus' => (string) $node->huidigeBehandelstatus,
            'cabinetPosition' => (string) $node->kabinetsappreciatie ?: null,
            'completed' => filter_var($node->afgedaan ?? 'false', FILTER_VALIDATE_BOOL),
            'deleted' => filter_var($node['verwijderd'] ?? 'false', FILTER_VALIDATE_BOOL),
        ];
    }

}
