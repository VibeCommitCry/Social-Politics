<?php

namespace App\Api\Enum;

enum DocumentField: string
{
    case Endpoint = 'document';
    case Id = 'Id';
    case Type = 'Soort';
    case DocumentNumber = 'Nummer';
    case Titel = 'Titel';
    case Subject = 'Onderwerp';
    case DiscussionYear = 'Vergaderjaar';
    case Room = 'Kamer';
    case SerialNumber = 'Volgnummer';
    case CiteTitel = 'Citeertitel';
    case Alias = 'Alias';
    case DateRegistration = 'DatumRegistratie';
    case DateOfReceipt = 'DatumOntvangst';
    case AppendixNumber = 'Bijlagenummer';
    case SenderReference = 'Afzender';
    case Organisation = 'Organisatie';
    case ContentType = 'ContentType';
    case ContentLength = 'ContentLength';
    case ChangedOn = 'GewijzigdOp';
    case ApiChangedOn = 'ApiGewijzigdOp';
    case Deleted = 'Verwijderd';
    case CurrentDocumentVersion_Id = 'HuidigeDocumentVersie_Id';
}