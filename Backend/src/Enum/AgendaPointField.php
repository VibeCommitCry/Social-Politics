<?php

declare(strict_types=1);

namespace App\Enum;

enum AgendaPointField: string
{
    case Endpoint = 'Agendapunt';
    case Id = 'Id';
    case Number = 'Nummer';
    case Subject = 'Onderwerp';
    case StartTime = 'Aanvangstijd';
    case EndTime = 'Eindtijd';
    case Sequence = 'Volgorde';
    case Heading = 'Rubriek';
    case Remark = 'Notitie';
    case ModifiedOn = 'GewijzigdOp';
    case ApiModifiedOn = 'ApiGewijzigdOp';
    case Deleted = 'Verwijderd';
    case ActivityId = 'Activiteit_Id';
    case Activity = 'Activiteit';
}
