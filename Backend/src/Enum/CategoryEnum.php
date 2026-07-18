<?php

namespace App\Enum;

enum CategoryEnum: string
{
    case FRACTION = "Fractie";
    case CASE = "Zaak";
    case VOTE = "Stemming";
    case DECISION = "Besluit";
    case AGENDAPOINT = "Agendapunt";
}