<?php

namespace App\Enum;

enum GovermentCaseField: string
{
    case Endpoint = 'Zaak';
    case Id = 'Id';
    case Number = 'Nummer';
    case Type = 'Soort';
    case Title = 'Titel';
    case CiteTitle = 'Citeertitel';
    case Alias = 'Alias';
    case Subject = 'Onderwerp';
    case Date = 'Datum';
    case Room = 'Kamer';
    case AdvanceNotice = 'Voorhang';
    case Deadline = 'Termijn';
    case MeetingYear = 'Vergaderjaar';
    case SubNumber = 'Ondernummer';
    case Completed = 'Afgedaan';
    case ModifiedOn = 'GewijzigdOp';
    case ApiModifiedOn = 'ApiGewijzigdOp';
    case Deleted = 'Verwijderd';
    case CabinetAppreciation = 'Kabinetsappreciatie';
    case CurrentStatus = 'HuidigeBehandelstatus';
    case DecisionArray = 'Besluit';
}