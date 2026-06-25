<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Entity\DevisLigne;
use App\Form\DevisLigneType;
use App\Form\DevisType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commercial/devis')]
class DevisController extends AbstractController
{
    // --- 1. LISTE DES DEVIS ---
    #[Route('/', name: 'commercial_devis_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $em->getRepository(Devis::class)->createQueryBuilder('d')
            ->leftJoin('d.client', 'c')->addSelect('c')
            ->orderBy('d.id', 'DESC');

        if ($recherche = $request->query->get('q')) {
            $queryBuilder->andWhere('LOWER(c.raisonSociale) LIKE LOWER(:recherche) OR LOWER(c.nom) LIKE LOWER(:recherche)')
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        $devisList = $paginator->paginate($queryBuilder, $request->query->getInt('page', 1), 15);

        return $this->render('commercial/devis/index.html.twig', ['devisList' => $devisList]);
    }

    // --- 2. CRÉATION DU DEVIS (En-tête) ---
    #[Route('/nouveau', name: 'commercial_devis_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $devis = new Devis();
        if ($this->getUser()) {
            $devis->setUser($this->getUser());
        }

        $form = $this->createForm(DevisType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($devis);
            $entityManager->flush();

            $this->addFlash('success', 'Le devis a été créé. Vous pouvez maintenant y ajouter des pièces.');

            // Redirection vers la consultation du devis pour ajouter les pièces
            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $devis->getId()])]);
            }

            return $this->redirectToRoute('commercial_devis_show', ['id' => $devis->getId()]);
        }

        return $this->render('commercial/devis/new.html.twig', ['form' => $form->createView(), 'devis' => $devis]);
    }

    // --- 3. CONSULTATION DU DEVIS ---
    #[Route('/{id}', name: 'commercial_devis_show', methods: ['GET'])]
    public function show(Devis $devis): Response
    {
        return $this->render('commercial/devis/show.html.twig', ['devis' => $devis]);
    }

    // --- 4. AJOUTER UNE PIÈCE (LIGNE) AU DEVIS ---
    #[Route('/{id}/ajouter-piece', name: 'commercial_devis_ligne_new', methods: ['GET', 'POST'])]
    public function addLigne(Request $request, Devis $devis, EntityManagerInterface $entityManager): Response
    {
        $ligne = new DevisLigne();
        $ligne->setDevis($devis);

        $form = $this->createForm(DevisLigneType::class, $ligne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ligne);
            $entityManager->flush();
            $this->addFlash('success', 'Pièce ajoutée au devis.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $devis->getId()])]);
            }

            return $this->redirectToRoute('commercial_devis_show', ['id' => $devis->getId()]);
        }

        return $this->render('commercial/devis/form_ligne.html.twig', ['form' => $form->createView()]);
    }

    // --- MODIFIER LE DEVIS (En-tête : Client, Date, Commercial) ---
    #[Route('/{id}/modifier', name: 'commercial_devis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Devis $devis, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DevisType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Les informations du devis ont été mises à jour.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('commercial_devis_index')]);
            }

            return $this->redirectToRoute('commercial_devis_index');
        }

        // On réutilise la vue 'new.html.twig' car elle contient exactement les bons champs
        return $this->render('commercial/devis/new.html.twig', [
            'form' => $form->createView(),
            'devis' => $devis,
        ]);
    }

    // --- 6. SUPPRIMER UNE PIÈCE (LIGNE) ---
    #[Route('/ligne/{id}/supprimer', name: 'commercial_devis_ligne_delete', methods: ['POST'])]
    public function deleteLigne(Request $request, DevisLigne $ligne, EntityManagerInterface $entityManager): Response
    {
        $devisId = $ligne->getDevis()->getId();

        // 1. Combien de fois cette paire a été commandée ?
        $countCommandees = 0;
        foreach ($ligne->getDevis()->getCommandes() as $cmd) {
            foreach ($cmd->getCommandeLignes() as $cl) {
                if ($cl->getPiece()->getId() === $ligne->getPiece()->getId() && $cl->getQuantite() === $ligne->getQuantite()) {
                    $countCommandees++;
                }
            }
        }

        // 2. Quelle est l'occurrence de la ligne qu'on essaie de supprimer ?
        $occurrenceIndex = 0;
        foreach ($ligne->getDevis()->getDevisLignes() as $dl) {
            if ($dl->getPiece()->getId() === $ligne->getPiece()->getId() && $dl->getQuantite() === $ligne->getQuantite()) {
                $occurrenceIndex++;
                if ($dl->getId() === $ligne->getId()) {
                    break;
                }
            }
        }

        if ($occurrenceIndex <= $countCommandees) {
            $this->addFlash('danger', 'Suppression impossible : cette ligne a déjà été transformée en commande.');
            return $this->redirectToRoute('commercial_devis_show', ['id' => $devisId]);
        }

        if ($this->isCsrfTokenValid('delete_ligne_' . $ligne->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ligne);
            $entityManager->flush();
            $this->addFlash('success', 'La ligne a été retirée du devis.');
        } else {
            $this->addFlash('danger', 'Action non autorisée.');
        }

        return $this->redirectToRoute('commercial_devis_show', ['id' => $devisId]);
    }
}
