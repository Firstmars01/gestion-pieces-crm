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

    // --- 2. AJOUTER UNE LIGNE DU DEVIS DANS LA COMMANDE ---
    #[Route('/{id}/ajouter-piece', name: 'commercial_commande_add_ligne', methods: ['GET', 'POST'])]
    public function addLigne(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $devis = $commande->getDevis();

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
            $quantiteDemandee = $commandeLigne->getQuantite();

            // SÉCURITÉ : Revérifier le reste avant de valider
            $dejaCommande = 0;
            foreach ($devis->getCommandes() as $cmd) {
                foreach ($cmd->getCommandeLignes() as $cl) {
                    if ($cl->getPiece() === $devisLigne->getPiece()) {
                        $dejaCommande += $cl->getQuantite();
                    }
                }
            }
            $reste = $devisLigne->getQuantite() - $dejaCommande;

            if ($quantiteDemandee > $reste) {
                $this->addFlash('danger', 'Quantité invalide. Il ne reste que ' . $reste . ' pièce(s) à commander.');
                return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $devis->getId()])]);
            }

            // COPIE STRICTE (Prix figé, pièce liée)
            $commandeLigne->setPiece($devisLigne->getPiece());
            $commandeLigne->setPrixUnitaire($devisLigne->getPrix());

            $em->persist($commandeLigne);
            $em->flush();

            $this->addFlash('success', 'La pièce a été ajoutée à la commande.');
            return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $devis->getId()])]);
        }

        return $this->render('commercial/commande/form_ligne.html.twig', ['form' => $form->createView()]);
    }
}
