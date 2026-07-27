<?php

namespace App\Enum;

enum VoteField: string
{
    case Endpoint = 'Stemming';
    case Id = 'Id';
    case DecisionId = 'Besluit_Id';
    case Type = 'Soort';
    case FractionSize = 'FractieGrootte';
    case ActorName = 'Naam';
    case ActorFraction = 'FractieNaam';
    case Mistake = 'Vergissing';
    case PersonId = 'Persoon_Id';
    case FractionId = 'Fractie_Id';
    case ModifiedOn = 'GewijzigdOp';
    case ApiModifiedOn = 'ApiGewijzigdOp';
    case Deleted = 'Verwijderd';
}