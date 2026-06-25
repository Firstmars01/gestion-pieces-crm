<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeLigne;
use App\Entity\Devis;
use App\Form\CommandeLigneType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commercial/commandes')]
class CommandeController extends AbstractController
{
    // --- 1. CRÉER UNE NOUVELLE COMMANDE VIDE ---
    #[Route('/devis/{id}/nouvelle', name: 'commercial_commande_new', methods: ['POST'])]
    public function newFromDevis(Request $request, Devis $devis, EntityManagerInterface $em): Response
    {
        // VÉRIFICATION MÉTIER : Date du devis
        if ($devis->getDateLimite() && new \DateTime() > $devis->getDateLimite()) {
            $this->addFlash('danger', 'Le délai de ce devis est dépassé, vous ne pouvez plus créer de commandes.');
            return $this->redirectToRoute('commercial_devis_show', ['id' => $devis->getId()]);
        }

        $commande = new Commande();
        $commande->setClient($devis->getClient());
        $commande->setDevis($devis);
        $commande->setNumero('CMD-' . date('YmdHis')); // Numéro temporaire

        $em->persist($commande);
        $em->flush();
        $this->addFlash('success', 'Nouvelle commande créée. Vous pouvez y ajouter des pièces.');

        return $this->redirectToRoute('commercial_devis_show', ['id' => $devis->getId()]);
    }

// --- 2. AJOUTER UNE LIGNE COMPLÈTE DU DEVIS DANS LA COMMANDE ---
    #[Route('/{id}/ajouter-piece', name: 'commercial_commande_add_ligne', methods: ['GET', 'POST'])]
    public function addLigne(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $devis = $commande->getDevis();

        // Règle : "On ne peut donc passer commande que si la date n'est pas postérieure au délai fixé"
        if ($devis->getDateLimite() && new \DateTime() > $devis->getDateLimite()) {
            $this->addFlash('danger', 'Le délai du devis est dépassé. Ajout impossible.');
            return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $devis->getId()])]);
        }

        $commandeLigne = new CommandeLigne();
        $commandeLigne->setCommande($commande);

        $form = $this->createForm(CommandeLigneType::class, $commandeLigne, ['devis' => $devis]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devisLigne = $form->get('devisLigne')->getData();

            // Règle : "Chaque ligne d'une commande doit correspondre à une ligne complète du devis (même pièce, même quantité)."
            $commandeLigne->setPiece($devisLigne->getPiece());
            $commandeLigne->setQuantite($devisLigne->getQuantite());

            // Règle : "Les montants doivent rester fixes"
            $commandeLigne->setPrixUnitaire($devisLigne->getPrix());

            $em->persist($commandeLigne);
            $em->flush();

            $this->addFlash('success', 'La ligne complète a été ajoutée à la commande.');
            return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $devis->getId()])]);
        }

        return $this->render('commercial/commande/form_ligne.html.twig', ['form' => $form->createView()]);
    }

    // --- 3. MODIFIER LES DATES D'UNE COMMANDE ---
    #[Route('/{id}/modifier', name: 'commercial_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(\App\Form\CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Les dates de la commande ont été mises à jour.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $commande->getDevis()->getId()])]);
            }
            return $this->redirectToRoute('commercial_devis_show', ['id' => $commande->getDevis()->getId()]);
        }

        return $this->render('commercial/commande/edit.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande
        ]);
    }

    // --- 4. SUPPRIMER UNE COMMANDE ---
    #[Route('/{id}/supprimer', name: 'commercial_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $devisId = $commande->getDevis()->getId();

        if ($this->isCsrfTokenValid('delete_commande_' . $commande->getId(), $request->request->get('_token'))) {
            $em->remove($commande);
            $em->flush();
            $this->addFlash('success', 'La commande a été supprimée.');
        }

        return $this->redirectToRoute('commercial_devis_show', ['id' => $devisId]);
    }


    // --- 6. SUPPRIMER UNE LIGNE DE COMMANDE ---
    #[Route('/ligne/{id}/supprimer', name: 'commercial_commande_ligne_delete', methods: ['POST'])]
    public function deleteLigne(Request $request, CommandeLigne $ligne, EntityManagerInterface $em): Response
    {
        $devisId = $ligne->getCommande()->getDevis()->getId();

        if ($this->isCsrfTokenValid('delete_commande_ligne_' . $ligne->getId(), $request->request->get('_token'))) {
            $em->remove($ligne);
            $em->flush();
            $this->addFlash('success', 'La pièce a été retirée de la commande.');
        }

        return $this->redirectToRoute('commercial_devis_show', ['id' => $devisId]);
    }
}
