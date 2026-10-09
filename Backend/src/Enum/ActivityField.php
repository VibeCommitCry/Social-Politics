<?php

declare(strict_types=1);

namespace App\Enum;

enum ActivityField: string
{
    case Endpoint = 'Activiteit';
    case Id = 'Id';
    case Type = 'Soort';
    case Number = 'Nummer';
    case Subject = 'Onderwerp';
    case DateType = 'DatumSoort';
    case Date = 'Datum';
    case StartTime = 'Aanvangstijd';
    case EndTime = 'Eindtijd';
    case Location = 'Locatie';
    case Private = 'Besloten';
    case Status = 'Status';
    case ParliamentaryYear = 'Vergaderjaar';
    case Room = 'Kamer';
    case Remark = 'Notitie';
    case LeadCommitteeName = 'VoortouwCommissieNaam';
    case LeadCommitteeAbbreviation = 'VoortouwCommissieAfkorting';
    case LeadCommitteeShortName = 'VoortouwCommissieKorteNaam';
    case LeadCommitteeId = 'VoortouwCommissie_Id';
    case RequestDate = 'Aanvraagdatum';
    case FirstExtensionRequestDate = 'DatumVerzoekEersteVerlenging';
    case FirstExtensionNoticeDate = 'DatumMededelingEersteVerlenging';
    case SecondExtensionRequestDate = 'DatumVerzoekTweedeVerlenging';
    case SecondExtensionNoticeDate = 'DatumMededelingTweedeVerlenging';
    case ExpiryDate = 'Vervaldatum';
    case ModifiedOn = 'GewijzigdOp';
    case ApiModifiedOn = 'ApiGewijzigdOp';
    case Deleted = 'Verwijderd';
}
