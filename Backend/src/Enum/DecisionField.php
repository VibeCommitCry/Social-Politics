<?php

namespace App\Enum;

enum DecisionField: string
{
    case Endpoint = 'Besluit';
    case Id = 'Id';
    case AgendaItemId = 'Agendapunt_Id';
    case VoteMethod = 'StemmingsSoort';
    case Type = 'BesluitSoort';
    case Text = 'BesluitTekst';
    case Remark = 'Opmerking';
    case Status = 'Status';
    case Sequence = 'AgendapuntZaakBesluitVolgorde';
    case ModifiedOn = 'GewijzigdOp';
    case ApiModifiedOn = 'ApiGewijzigdOp';
    case Deleted = 'Verwijderd';
}