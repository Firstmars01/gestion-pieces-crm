<?php

namespace App\Controller;

use App\Entity\Piece;
use App\Entity\PieceComposition;
use App\Form\PieceType;
use App\Repository\PieceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/atelier')]
class PieceController extends AbstractController
{
    #[Route('/stock', name: 'atelier_stock')]
    public function index(PieceRepository $pieceRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $queryBuilder = $pieceRepository->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC'); // Tri par défaut pour éviter le bug des doublons de pagination

        $recherche = $request->query->get('q');

        if ($recherche) {
            $queryBuilder->andWhere('LOWER(p.reference) LIKE LOWER(:recherche) OR LOWER(p.libelle) LIKE LOWER(:recherche) OR LOWER(p.type) LIKE LOWER(:recherche)')
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        $pieces = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('atelier/piece/index.html.twig', [
            'pieces' => $pieces,
        ]);
    }

    #[Route('/piece/nouvelle', name: 'atelier_piece_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $piece = new Piece();
        $form = $this->createForm(PieceType::class, $piece);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($piece);
            $entityManager->flush();

            $this->addFlash('success', 'La pièce '.$piece->getReference().' a été créée avec succès !');

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'redirect' => $this->generateUrl('atelier_stock'),
                ]);
            }

            return $this->redirectToRoute('atelier_stock');
        }

        return $this->render('atelier/piece/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/piece/{id}/modifier', name: 'atelier_piece_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Piece $piece, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PieceType::class, $piece);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La pièce '.$piece->getReference().' a été modifiée avec succès !');

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'redirect' => $this->generateUrl('atelier_stock'),
                ]);
            }

            return $this->redirectToRoute('atelier_stock');
        }

        return $this->render('atelier/piece/new.html.twig', [
            'form' => $form->createView(),
            'piece' => $piece,
        ]);
    }

    #[Route('/piece/{id}/supprimer', name: 'atelier_piece_delete', methods: ['POST'])]
    public function delete(Request $request, Piece $piece, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$piece->getId(), $request->request->get('_token'))) {
            // Sécurité : On vérifie si la pièce est liée à des composants enfants
            $isUsedAsComponent = $entityManager->getRepository(PieceComposition::class)->findOneBy([
                'pieceEnfant' => $piece,
            ]);

            if ($isUsedAsComponent) {
                $this->addFlash('danger', 'Impossible de supprimer la pièce car elle est utilisée comme composant dans une autre pièce.');

                return $this->redirectToRoute('atelier_stock');
            }

            // Sécurité : On vérifie si elle possède une Gamme de fabrication (Relation OneToOne)
            if (null !== $piece->getGamme()) {
                $this->addFlash('danger', 'Impossible de supprimer cette pièce car une gamme de fabrication lui est associée.');

                return $this->redirectToRoute('atelier_stock');
            }

            $entityManager->remove($piece);
            $entityManager->flush();

            $this->addFlash('success', 'La pièce a été supprimée de la base de données.');
        } else {
            $this->addFlash('danger', 'Action non autorisée (Token CSRF invalide).');
        }

        return $this->redirectToRoute('atelier_stock');
    }
}
