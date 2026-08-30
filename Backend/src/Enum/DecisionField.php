<?php

namespace App\Enum;

enum DecisionField: string
{
    case Endpoint = 'Besluit';
    case Id = 'Id';
    case AgendaItemId = 'Agendapunt_Id';
    case Type = 'Soort';              
    case Text = 'BesluitTekst';       
    case Remark = 'Notitie';           
    case Status = 'Status';
    case Sequence = 'Volgorde';
    case ModifiedOn = 'GewijzigdOp';
    case ApiModifiedOn = 'ApiGewijzigdOp';
    case Deleted = 'Verwijderd';
}