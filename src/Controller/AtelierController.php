<?php

namespace App\Controller;

use App\Entity\Gamme;
use App\Entity\Piece;
use App\Entity\PieceComposition;
use App\Form\GammeType;
use App\Form\PieceType;
use App\Repository\GammeRepository;
use App\Repository\PieceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
        return $this->render('atelier/piece/piece_new.html.twig', [
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
        return $this->render('atelier/piece/piece_new.html.twig', [
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

    #[Route('/stock', name: 'atelier_stock')]
    public function index(PieceRepository $pieceRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $queryBuilder = $pieceRepository->createQueryBuilder('p');

        // Récupération du terme de recherche
        $recherche = $request->query->get('q');

        // Si une recherche est faite, on filtre la requête (insensible à la casse)
        if ($recherche) {
            $queryBuilder->andWhere('LOWER(p.reference) LIKE LOWER(:recherche) OR LOWER(p.libelle) LIKE LOWER(:recherche) OR LOWER(p.type) LIKE LOWER(:recherche)')
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        $pieces = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('atelier/piece/stock.html.twig', [
            'pieces' => $pieces,
        ]);
    }

    #[Route('/atelier/gammes', name: 'atelier_gamme_index', methods: ['GET'])]
    public function gammeIndex(Request $request, GammeRepository $gammeRepository, PaginatorInterface $paginator): Response
    {
        // On utilise l'alias 'g' pour Gamme, et on joint la Pièce ('p') et l'Utilisateur ('u')
        // Les jointures permettent d'optimiser les requêtes et de faire des recherches sur ces tables
        $queryBuilder = $gammeRepository->createQueryBuilder('g')
            ->leftJoin('g.piece', 'p')
            ->addSelect('p')
            ->leftJoin('g.user', 'u')
            ->addSelect('u');

        // Récupération du terme de recherche
        $recherche = $request->query->get('q');

        // Recherche insensible à la casse sur le nom de la gamme, la référence de la pièce ou le nom du responsable
        if ($recherche) {
            $queryBuilder->andWhere('LOWER(g.libelle) LIKE LOWER(:recherche) OR LOWER(p.reference) LIKE LOWER(:recherche) OR LOWER(u.nom) LIKE LOWER(:recherche) OR LOWER(u.prenom) LIKE LOWER(:recherche)')
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        $gammes = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20 // Nombre de gammes par page
        );

        return $this->render('atelier/gamme/gamme_index.html.twig', [
            'gammes' => $gammes,
        ]);
    }

    #[Route('/atelier/gammes/new', name: 'atelier_gamme_new', methods: ['GET', 'POST'])]
    public function gammeNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $gamme = new Gamme();
        $form = $this->createForm(GammeType::class, $gamme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($gamme);
            $entityManager->flush();

            $this->addFlash('success', 'La gamme a été créée avec succès.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_gamme_index')]);
            }
            return $this->redirectToRoute('atelier_gamme_index');
        }

        return $this->render('atelier/gamme/gamme_new.html.twig', [
            'form' => $form->createView(),
            'gamme' => $gamme,
        ]);
    }

    #[Route('/atelier/gammes/{id}/edit', name: 'atelier_gamme_edit', methods: ['GET', 'POST'])]
    public function gammeEdit(Request $request, Gamme $gamme, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GammeType::class, $gamme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La gamme a été mise à jour.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_gamme_index')]);
            }
            return $this->redirectToRoute('atelier_gamme_index');
        }

        return $this->render('atelier/gamme/gamme_new.html.twig', [
            'form' => $form->createView(),
            'gamme' => $gamme,
        ]);
    }

    #[Route('/atelier/gammes/{id}', name: 'atelier_gamme_delete', methods: ['POST'])]
    public function gammeDelete(Request $request, Gamme $gamme, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_gamme_' . $gamme->getId(), $request->request->get('_token'))) {
            $entityManager->remove($gamme);
            $entityManager->flush();
            $this->addFlash('success', 'La gamme a été supprimée.');
        }

        return $this->redirectToRoute('atelier_gamme_index');
    }
}
