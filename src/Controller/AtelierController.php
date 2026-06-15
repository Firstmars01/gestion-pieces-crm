<?php

namespace App\Controller;

use App\Entity\Piece;
use App\Entity\PieceComposition;
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
            $entityManager->persist($piece);
            $entityManager->flush();

            // On ajoute le message flash normalement
            $this->addFlash('success', 'La pièce '.$piece->getReference().' a été créée avec succès !');

            // Si c'est notre JavaScript qui a envoyé le formulaire, on renvoie une réponse JSON
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'redirect' => $this->generateUrl('atelier_stock'),
                ]);
            }

            // Comportement normal si javascript est désactivé
            return $this->redirectToRoute('atelier_stock');
        }

        // 5. On affiche la page avec le formulaire
        return $this->render('atelier/piece_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ======================================================
    // MODIFIER UNE PIÈCE
    // ======================================================
    #[Route('/piece/{id}/modifier', name: 'atelier_piece_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Piece $piece, EntityManagerInterface $entityManager): Response
    {
        // On crée le formulaire avec la pièce existante
        $form = $this->createForm(PieceType::class, $piece);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le persist() n'est pas nécessaire en modification, flush() suffit
            $entityManager->flush();

            $this->addFlash('success', 'La pièce '.$piece->getReference().' a été modifiée avec succès !');

            // Si c'est notre JavaScript qui a envoyé le formulaire (Modale AJAX)
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'redirect' => $this->generateUrl('atelier_stock'),
                ]);
            }

            return $this->redirectToRoute('atelier_stock');
        }

        // 💡 ASTUCE : On réutilise exactement le même fichier Twig que pour la création !
        return $this->render('atelier/piece_new.html.twig', [
            'form' => $form->createView(),
            'piece' => $piece, // On passe la pièce si on veut adapter le titre dans Twig plus tard
        ]);
    }

    // ======================================================
    // SUPPRIMER UNE PIÈCE
    // ======================================================
    #[Route('/piece/{id}/supprimer', name: 'atelier_piece_delete', methods: ['POST'])]
    public function delete(Request $request, Piece $piece, EntityManagerInterface $entityManager): Response
    {
        // Vérification de sécurité du token CSRF
        if ($this->isCsrfTokenValid('delete'.$piece->getId(), $request->request->get('_token'))) {
            // On cherche si cette pièce est utilisée comme composant enfant
            $isUsedAsComponent = $entityManager->getRepository(PieceComposition::class)->findOneBy([
                'pieceEnfant' => $piece,
            ]);

            // Si on trouve au moins un résultat, on bloque la suppression
            if ($isUsedAsComponent) {
                $this->addFlash('danger', 'Impossible de supprimer la pièce car elle est utilisée comme composant dans une autre pièce.');

                return $this->redirectToRoute('atelier_stock');
            }

            // Si elle n'est pas utilisée, on procède à la suppression
            $entityManager->remove($piece);
            $entityManager->flush();

            $this->addFlash('success', 'La pièce a été supprimée de la base de données.');
        } else {
            $this->addFlash('danger', 'Action non autorisée (Token CSRF invalide).');
        }

        return $this->redirectToRoute('atelier_stock');
    }
}
