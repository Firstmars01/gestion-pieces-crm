<?php

namespace App\Controller;

use App\Entity\Piece;
use App\Form\PieceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/atelier')]
class AtelierController extends AbstractController
{
    #[Route('/piece/nouvelle', name: 'atelier_piece_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. On crée une nouvelle instance de Pièce
        $piece = new Piece();

        // 2. On crée le formulaire en le liant à notre objet Pièce
        $form = $this->createForm(PieceType::class, $piece);

        // 3. On demande au formulaire d'analyser la requête HTTP (POST)
        $form->handleRequest($request);

        // 4. Si le formulaire est soumis ET que toutes nos règles (Assert) sont valides
        if ($form->isSubmitted() && $form->isValid()) {

            // On prépare la sauvegarde et on exécute
            $entityManager->persist($piece);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'La pièce ' . $piece->getReference() . ' a été créée avec succès !');

            // On redirige vers la page du stock (que tu as déjà créée)
            return $this->redirectToRoute('atelier_stock');
        }

        // 5. On affiche la page avec le formulaire
        return $this->render('atelier/piece_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
