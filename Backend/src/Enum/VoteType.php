<?php

declare(strict_types=1);

namespace App\Enum;

enum VoteType: string
{
    case For = 'Voor';
    case Against = 'Tegen';
    case Abstain = 'Niet deelgenomen';
}
