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
    #[Route('/devis/{id}/nouvelle', name: 'commercial_commande_new', methods: ['POST'])]
    public function newFromDevis(Request $request, Devis $devis, EntityManagerInterface $em): Response
    {
        if ($devis->getDateLimite() && new \DateTime() > $devis->getDateLimite()) {
            $this->addFlash('danger', 'Le délai est dépassé.');
            return $this->redirectToRoute('commercial_devis_show', ['id' => $devis->getId()]);
        }

        $commande = new Commande();
        $commande->setClient($devis->getClient());

        // C'EST ICI QUE ÇA CHANGE : On ajoute le devis à la LISTE de la commande
        $commande->addDevisList($devis);
        $commande->setNumero('CMD-' . date('YmdHis'));

        $em->persist($commande);
        $em->flush();
        $this->addFlash('success', 'Nouvelle commande créée.');

        return $this->redirectToRoute('commercial_devis_show', ['id' => $devis->getId()]);
    }

    #[Route('/{id}/ajouter-piece', name: 'commercial_commande_add_ligne', methods: ['GET', 'POST'])]
    public function addLigne(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $commandeLigne = new CommandeLigne();
        $commandeLigne->setCommande($commande);

        $form = $this->createForm(CommandeLigneType::class, $commandeLigne, ['commande' => $commande]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devisLigne = $form->get('devisLigne')->getData();

            // COPIE STRICTE DES DONNÉES DU DEVIS !
            $commandeLigne->setPiece($devisLigne->getPiece());
            $commandeLigne->setQuantite($devisLigne->getQuantite()); // Aucune saisie possible
            $commandeLigne->setPrixUnitaire($devisLigne->getPrix());

            $em->persist($commandeLigne);
            $em->flush();

            $this->addFlash('success', 'La ligne complète a été ajoutée à la commande.');
            return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()])]);
        }

        return $this->render('commercial/commande/form_ligne.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/modifier', name: 'commercial_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(\App\Form\CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Les dates ont été mises à jour.');
            return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()])]);
        }

        return $this->render('commercial/commande/edit.html.twig', ['form' => $form->createView(), 'commande' => $commande]);
    }

    #[Route('/{id}/supprimer', name: 'commercial_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $devisId = $commande->getDevisList()->first()->getId();

        if ($this->isCsrfTokenValid('delete_commande_' . $commande->getId(), $request->request->get('_token'))) {
            $em->remove($commande);
            $em->flush();
            $this->addFlash('success', 'La commande a été supprimée.');
        }

        return $this->redirectToRoute('commercial_devis_show', ['id' => $devisId]);
    }

    #[Route('/ligne/{id}/supprimer', name: 'commercial_commande_ligne_delete', methods: ['POST'])]
    public function deleteLigne(Request $request, CommandeLigne $ligne, EntityManagerInterface $em): Response
    {
        $devisId = $ligne->getCommande()->getDevisList()->first()->getId();

        if ($this->isCsrfTokenValid('delete_commande_ligne_' . $ligne->getId(), $request->request->get('_token'))) {
            $em->remove($ligne);
            $em->flush();
            $this->addFlash('success', 'La pièce a été retirée de la commande.');
        }

        return $this->redirectToRoute('commercial_devis_show', ['id' => $devisId]);
    }

    #[Route('/{id}/lier-devis', name: 'commercial_commande_lier_devis', methods: ['GET', 'POST'])]
    public function lierDevis(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(\App\Form\CommandeAddDevisType::class, null, ['commande' => $commande]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nouveauDevis = $form->get('devis')->getData();

            // On lie le devis à la commande !
            $commande->addDevisList($nouveauDevis);
            $em->flush();

            $this->addFlash('success', 'Le devis a été lié. Vous pouvez maintenant ajouter ses pièces à la commande.');

            // On redirige vers la page où on était (le 1er devis de la liste par défaut)
            return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()])]);
        }

        return $this->render('commercial/commande/form_lier_devis.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande
        ]);
    }
}
