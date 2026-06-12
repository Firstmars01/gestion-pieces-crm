<?php

namespace App\Controller;

use App\Repository\PieceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class StockController extends AbstractController
{
    // Route renommée pour correspondre à la navigation: 'atelier_stock'
    #[Route('/atelier/stock', name: 'atelier_stock')]
    public function index(PieceRepository $pieceRepository): Response
    {
        // Seuls les utilisateurs ayant le rôle ROLE_ATELIER ou ROLE_ADMIN peuvent accéder
        if (! $this->isGranted('ROLE_ATELIER') && ! $this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès réservé aux ateliers.');
        }

        $pieces = $pieceRepository->findAll();

        return $this->render('atelier/stock.html.twig', [
            'pieces' => $pieces,
        ]);
    }
}

