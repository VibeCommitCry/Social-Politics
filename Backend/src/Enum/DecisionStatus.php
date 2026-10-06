<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * NOTE: the documentation page rendered these four values concatenated
 * with no separators ("BesluitConcept voorstelNog te verwerken besluitVoorstel").
 * The split below is a best-effort guess — verify against a live OData
 * response before relying on this in production.
 */
enum DecisionStatus: string
{
    case Decision = 'Besluit';
    case DraftProposal = 'Concept voorstel';
    case Pending = 'Nog te verwerken besluit';
    case Proposal = 'Voorstel';
}
