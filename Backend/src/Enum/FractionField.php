<?php

namespace App\Enum;

enum FractionField: string
{
    case Endpoint = 'fractie';
    case Id = 'Id';
    case Number = 'Nummer';
    case Abbreviation = 'Afkorting';
    case DutchName = 'NaamNL';
    case EnglishName = 'NaamEN';
    case SeatCount = 'AantalZetels';
    case VoteCount = 'AantalStemmen';
    case ActiveFrom = 'DatumActief';
    case ActiveUntil = 'DatumInactief';
    case ContentType = 'ContentType';
    case ContentLength = 'ContentLength';
    case ModifiedAt = 'GewijzigdOp';
    case ApiModifiedAt = 'ApiGewijzigdOp';
    case Deleted = 'Verwijderd';
}