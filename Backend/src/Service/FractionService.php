<?php

namespace App\Service;



class FractionService
{
    public function __construct(
    ) {
    }

    /**
     * Extracts fractie fields from a raw <tk:fractie> XML node into a plain array.
     * Keeps XML-parsing concerns out of the repository/entity layer.
     */
    public function extractFractieData(\SimpleXMLElement $node): array
    {
        return [
            'id' => (string) $node['id'],
            'abbreviation' => (string) $node->afkorting,
            'nameNl' => (string) $node->naamNL,
            'nameEn' => (string) $node->naamEN,
            'seatCount' => (int) $node->aantalZetels,
            'deleted' => filter_var($node['verwijderd'] ?? 'false', FILTER_VALIDATE_BOOL),
        ];
    }

}