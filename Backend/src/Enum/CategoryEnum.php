<?php

declare(strict_types=1);

namespace App\Enum;

enum CategoryEnum: string
{
    case FRACTION = "Fractie";
    case GOVERMENTCASE = "Zaak";
    case VOTE = "Stemming";
    case DECISION = "Besluit";
    case AGENDAPOINT = "Agendapunt";
}
