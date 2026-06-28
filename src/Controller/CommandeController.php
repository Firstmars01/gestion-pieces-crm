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
    /**
     * Petite fonction privée pour vérifier si la commande est verrouillée (passée et validée).
     */
    private function isCommandeVerrouillee(Commande $commande): bool
    {
        return $commande->isLivree();
    }

    #[Route('/devis/{id}/nouvelle', name: 'commercial_commande_new', methods: ['GET', 'POST'])]
    public function newFromDevis(Request $request, Devis $devis, EntityManagerInterface $em): Response
    {
        if ($devis->getDateLimite() && new \DateTime() > $devis->getDateLimite()) {
            $this->addFlash('danger', 'Le délai est dépassé.');

            return $this->redirectToRoute('commercial_devis_show', ['id' => $devis->getId()]);
        }

        $commande = new Commande();
        $commande->setClient($devis->getClient());
        $commande->addDevisList($devis);
        $commande->setNumero('CMD-'.date('YmdHis'));

        $form = $this->createForm(\App\Form\CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($commande);
            $em->flush();
            $this->addFlash('success', 'Nouvelle commande créée avec succès.');

            return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $devis->getId()])]);
        }

        return $this->render('commercial/commande/edit.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/livrer', name: 'commercial_commande_livrer', methods: ['POST'])]
    public function livrer(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($this->isCommandeVerrouillee($commande)) {
            $this->addFlash('danger', 'Commande déjà expédiée.');
            return $this->redirectToRoute('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()]);
        }

        // --- NOUVELLE SÉCURITÉ : La commande est-elle vide ? ---
        if ($commande->getCommandeLignes()->isEmpty()) {
            $this->addFlash('danger', 'Impossible d\'expédier : la commande est vide. Veuillez d\'abord y ajouter des pièces.');
            return $this->redirectToRoute('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()]);
        }

        if ($this->isCsrfTokenValid('livrer_commande_' . $commande->getId(), $request->request->get('_token'))) {
            $erreursStock = [];

            // 1. On vérifie le stock une dernière fois
            foreach ($commande->getCommandeLignes() as $ligne) {
                $piece = $ligne->getPiece();
                if ($piece->getQuantiteStock() < $ligne->getQuantite()) {
                    $manque = $ligne->getQuantite() - $piece->getQuantiteStock();
                    $erreursStock[] = $piece->getReference() . ' (Manque : ' . $manque . ')';
                }
            }

            if (count($erreursStock) > 0) {
                $this->addFlash('danger', 'Impossible d\'expédier. Stock insuffisant pour : ' . implode(', ', $erreursStock));
            } else {
                // 2. On DÉDUIT les stocks !
                foreach ($commande->getCommandeLignes() as $ligne) {
                    $piece = $ligne->getPiece();
                    $piece->setQuantiteStock($piece->getQuantiteStock() - $ligne->getQuantite());
                }

                // 3. On verrouille définitivement la commande
                $commande->setIsLivree(true);
                $em->flush();

                $this->addFlash('success', 'Commande expédiée ! Les stocks ont été déduits de l\'entrepôt.');
            }
        }

        return $this->redirectToRoute('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()]);
    }

    #[Route('/{id}/ajouter-piece', name: 'commercial_commande_add_ligne', methods: ['GET', 'POST'])]
    public function addLigne(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        // SÉCURITÉ : La commande est-elle passée ?
        if ($this->isCommandeVerrouillee($commande)) {
            $this->addFlash('danger', 'Action impossible : Cette commande a déjà été livrée et est archivée.');

            return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()])]);
        }

        $commandeLigne = new CommandeLigne();
        $commandeLigne->setCommande($commande);

        $form = $this->createForm(CommandeLigneType::class, $commandeLigne, ['commande' => $commande]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devisLigne = $form->get('devisLigne')->getData();

            $commandeLigne->setPiece($devisLigne->getPiece());
            $commandeLigne->setQuantite($devisLigne->getQuantite());
            $commandeLigne->setPrixUnitaire($devisLigne->getPrix());

            $em->persist($commandeLigne);
            $em->flush();

            $this->addFlash('success', 'La ligne complète a été ajoutée à la commande.');

            $referer = $request->headers->get('referer');
            $redirectUrl = $referer ?: $this->generateUrl('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()]);

            return $this->json(['redirect' => $redirectUrl]);
        }

        return $this->render('commercial/commande/form_ligne.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/modifier', name: 'commercial_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($this->isCommandeVerrouillee($commande)) {
            $this->addFlash('danger', 'Action impossible : Cette commande a déjà été livrée.');
            return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()])]);
        }

        $form = $this->createForm(\App\Form\CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Les dates ont été mises à jour.');

            $referer = $request->headers->get('referer');
            $redirectUrl = $referer ?: $this->generateUrl('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()]);

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $redirectUrl]);
            }
            return $this->redirect($redirectUrl);
        }

        return $this->render('commercial/commande/edit.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande
        ]);
    }

    #[Route('/{id}/supprimer', name: 'commercial_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        // SÉCURITÉ : La commande est-elle passée ?
        if ($this->isCommandeVerrouillee($commande)) {
            $this->addFlash('danger', 'Action impossible : Cette commande a déjà été livrée et archivée.');
            $referer = $request->headers->get('referer');

            return $referer ? $this->redirect($referer) : $this->redirectToRoute('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()]);
        }

        if ($this->isCsrfTokenValid('delete_commande_'.$commande->getId(), $request->request->get('_token'))) {
            $em->remove($commande);
            $em->flush();
            $this->addFlash('success', 'La commande a été supprimée.');
        }

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()]);
    }

    #[Route('/ligne/{id}/supprimer', name: 'commercial_commande_ligne_delete', methods: ['POST'])]
    public function deleteLigne(Request $request, CommandeLigne $ligne, EntityManagerInterface $em): Response
    {
        $commande = $ligne->getCommande();

        // SÉCURITÉ : La commande est-elle passée ?
        if ($this->isCommandeVerrouillee($commande)) {
            $this->addFlash('danger', 'Action impossible : Impossible de retirer une pièce d\'une commande déjà livrée.');
            $referer = $request->headers->get('referer');

            return $referer ? $this->redirect($referer) : $this->redirectToRoute('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()]);
        }

        if ($this->isCsrfTokenValid('delete_commande_ligne_'.$ligne->getId(), $request->request->get('_token'))) {
            $em->remove($ligne);
            $em->flush();
            $this->addFlash('success', 'La pièce a été retirée de la commande.');
        }

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()]);
    }

    #[Route('/{id}/lier-devis', name: 'commercial_commande_lier_devis', methods: ['GET', 'POST'])]
    public function lierDevis(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        // SÉCURITÉ : La commande est-elle passée ?
        if ($this->isCommandeVerrouillee($commande)) {
            $this->addFlash('danger', 'Action impossible : Cette commande a déjà été livrée.');

            return $this->json(['redirect' => $this->generateUrl('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()])]);
        }

        $form = $this->createForm(\App\Form\CommandeAddDevisType::class, null, ['commande' => $commande]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nouveauDevis = $form->get('devis')->getData();

            $commande->addDevisList($nouveauDevis);
            $em->flush();

            $this->addFlash('success', 'Le devis a été lié. Vous pouvez maintenant ajouter ses pièces à la commande.');

            $referer = $request->headers->get('referer');
            $redirectUrl = $referer ?: $this->generateUrl('commercial_devis_show', ['id' => $commande->getDevisList()->first()->getId()]);

            return $this->json(['redirect' => $redirectUrl]);
        }

        return $this->render('commercial/commande/form_lier_devis.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande,
        ]);
    }
}
