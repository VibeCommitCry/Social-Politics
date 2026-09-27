<?php

namespace App\Service;

use App\Repository\FractionRepository;



class FractionService
{
    public function __construct(
        private readonly FractionRepository $fractionRepository
    ) {
    }

    public function getFractionsWithSeats():array
    {   
        $d = $this->fractionRepository->findActive();
        return [];
    }

}