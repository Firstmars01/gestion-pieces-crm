<?php

namespace App\Controller;

use App\Entity\Fournisseur;
use App\Entity\Piece;
use App\Form\FournisseurType;
use App\Form\FournisseurPiecePrixCatalogueType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/achats/fournisseurs')]
class FournisseurController extends AbstractController
{
    #[Route('/', name: 'achats_fournisseur_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $em->getRepository(Fournisseur::class)->createQueryBuilder('f')
            ->orderBy('f.id', 'ASC');

        // Gestion de la barre de recherche
        if ($recherche = $request->query->get('q')) {
            $queryBuilder->andWhere(
                'LOWER(f.raisonSociale) LIKE LOWER(:recherche) OR '.
                'LOWER(f.email) LIKE LOWER(:recherche) OR '.
                'f.telephone LIKE :recherche'
            )
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        $fournisseurs = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20 // 20 fournisseurs par page
        );

        return $this->render('achats/fournisseur/index.html.twig', [
            'fournisseurs' => $fournisseurs,
        ]);
    }

    #[Route('/new', name: 'achats_fournisseur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $fournisseur = new Fournisseur();
        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($fournisseur);
            $em->flush();

            $this->addFlash('success', 'Le fournisseur a été ajouté avec succès.');

            return $this->redirectToRoute('achats_fournisseur_index');
        }

        return $this->render('achats/fournisseur/new.html.twig', [
            'fournisseur' => $fournisseur,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'achats_fournisseur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fournisseur $fournisseur, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Les informations du fournisseur ont été mises à jour.');

            return $this->redirectToRoute('achats_fournisseur_index');
        }

        // On utilise le même template "new.html.twig" pour la modification (comme dans ton CRM)
        return $this->render('achats/fournisseur/new.html.twig', [
            'fournisseur' => $fournisseur,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'achats_fournisseur_delete', methods: ['POST'])]
    public function delete(Request $request, Fournisseur $fournisseur, EntityManagerInterface $em): Response
    {
        // Vérification de sécurité avec le token CSRF généré dans ta vue Twig
        if ($this->isCsrfTokenValid('delete_fournisseur_'.$fournisseur->getId(), $request->request->get('_token'))) {
            // Note : Grâce au "nullable: true" sur la relation dans Piece.php,
            // supprimer le fournisseur mettra simplement à jour les pièces concernées
            // pour qu'elles n'aient plus de fournisseur_id.
            $em->remove($fournisseur);
            $em->flush();

            $this->addFlash('success', 'Le fournisseur a été supprimé.');
        }

        return $this->redirectToRoute('achats_fournisseur_index');
    }

    #[Route('/{id}/show', name: 'achats_fournisseur_show', methods: ['GET'])]
    public function show(Fournisseur $fournisseur): Response
    {
        return $this->render('achats/fournisseur/show.html.twig', [
            'fournisseur' => $fournisseur,
        ]);
    }

    #[Route('/{id}/add-piece', name: 'achats_fournisseur_add_piece', methods: ['GET', 'POST'])]
    public function addPiece(Request $request, Fournisseur $fournisseur, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(\App\Form\FournisseurAddPieceType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $piece = $form->get('piece')->getData();

            // On récupère le nouveau prix d'achat saisi
            $nouveauPrix = $form->get('prixAchat')->getData();

            if ($piece) {
                $piece->setFournisseur($fournisseur);

                if ($nouveauPrix !== null) {
                    // On met à jour la propriété de la pièce
                    $piece->setPrixCatalogue($nouveauPrix);
                }

                $em->flush();

                $this->addFlash('success', 'La pièce a bien été associée avec son nouveau prix d\'achat.');
            }

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'redirect' => $this->generateUrl('achats_fournisseur_show', ['id' => $fournisseur->getId()])
                ]);
            }
            return $this->redirectToRoute('achats_fournisseur_show', ['id' => $fournisseur->getId()]);
        }

        return $this->render('achats/fournisseur/add_piece.html.twig', [
            'fournisseur' => $fournisseur,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{fournisseur}/pieces/{piece}/prix-catalogue', name: 'achats_fournisseur_piece_prix_catalogue', methods: ['GET', 'POST'])]
    public function editPiecePrixCatalogue(Request $request, Fournisseur $fournisseur, Piece $piece, EntityManagerInterface $em): Response
    {
        if ($piece->getFournisseur() !== $fournisseur) {
            throw $this->createNotFoundException('Cette pièce n\'est pas liée à ce fournisseur.');
        }

        $form = $this->createForm(FournisseurPiecePrixCatalogueType::class, $piece);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le prix catalogue de la pièce a été mis à jour.');

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'redirect' => $this->generateUrl('achats_fournisseur_show', ['id' => $fournisseur->getId()]),
                ]);
            }

            return $this->redirectToRoute('achats_fournisseur_show', ['id' => $fournisseur->getId()]);
        }

        return $this->render('achats/fournisseur/edit_piece_prix_catalogue.html.twig', [
            'fournisseur' => $fournisseur,
            'piece' => $piece,
            'form' => $form->createView(),
        ]);
    }
}
