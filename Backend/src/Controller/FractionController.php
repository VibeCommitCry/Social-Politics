<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\FractionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class FractionController extends AbstractController
{
    public function __construct(
        private readonly FractionService $fractionService
    ) {}

    #[Route('/fraction', name: 'Fraction with seats')]
    public function index(): JsonResponse
    {
        $fractions = $this->fractionService->getFractionsWithSeats();

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/FractionController.php',
        ]);
    }
}
