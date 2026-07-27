<?php

namespace App\Enum;

enum DecisionVoteMethod: string
{
    case RollCall = 'Hoofdelijk';
    case ShowOfHands = 'Met handopsteken';
    case WithoutVote = 'Zonder stemming';
}