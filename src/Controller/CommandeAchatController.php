<?php

namespace App\Controller;

use App\Entity\CmdAchatLigne;
use App\Entity\CommandeAchat;
use App\Form\CommandeAchatType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/achats/commandes')]
class CommandeAchatController extends AbstractController
{
    #[Route('/', name: 'achats_commande_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        // 1. On ajoute une colonne "virtuelle" cachée (statut_tri) pour pouvoir trier dessus
        // Poids : 1 = Retard, 2 = En attente, 3 = Livrée
        $queryBuilder = $em->getRepository(CommandeAchat::class)->createQueryBuilder('c')
            ->leftJoin('c.fournisseur', 'f')->addSelect('f')
            ->addSelect('
                CASE
                    WHEN c.dateReelle IS NOT NULL THEN 3
                    WHEN c.datePrevue < :maintenant THEN 1
                    ELSE 2
                END AS HIDDEN statut_tri
            ')
            ->setParameter('maintenant', new \DateTime())
            ->orderBy('c.dateCommande', 'DESC');

        // Gestion de la barre de recherche
        if ($recherche = trim((string) $request->query->get('q'))) {
            $queryBuilder->andWhere('LOWER(f.raisonSociale) LIKE LOWER(:recherche) OR c.id = :idRecherche')
                ->setParameter('recherche', '%'.$recherche.'%')
                ->setParameter('idRecherche', is_numeric($recherche) ? $recherche : 0);
        }

        $commandesList = $paginator->paginate($queryBuilder, $request->query->getInt('page', 1), 15);

        return $this->render('achats/commande/index.html.twig', [
            'commandesList' => $commandesList,
        ]);
    }

    #[Route('/new', name: 'achats_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $commande = new CommandeAchat();
        $form = $this->createForm(CommandeAchatType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($commande);
            $em->flush();

            $this->addFlash('success', 'La commande d\'achat a été créée. Vous pouvez maintenant y ajouter des pièces.');

            // Si la requête vient de la modale AJAX, on renvoie du JSON
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'redirect' => $this->generateUrl('achats_commande_show', ['id' => $commande->getId()]),
                ]);
            }

            // Fallback classique
            return $this->redirectToRoute('achats_commande_show', ['id' => $commande->getId()]);
        }

        return $this->render('achats/commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'achats_commande_show', methods: ['GET'])]
    public function show(CommandeAchat $commande): Response
    {
        return $this->render('achats/commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'achats_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CommandeAchat $commande, EntityManagerInterface $em): Response
    {
        // 1. On mémorise si la commande était DÉJÀ livrée avant la modification
        $etaitDejaLivree = null !== $commande->getDateReelle();

        $form = $this->createForm(CommandeAchatType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 2. On vérifie l'état après soumission du formulaire
            $estMaintenantLivree = null !== $commande->getDateReelle();

            // 3. LA MAGIE : Si elle n'était pas livrée, et qu'elle l'est maintenant !
            if (!$etaitDejaLivree && $estMaintenantLivree) {
                // On boucle sur toutes les lignes de la commande
                foreach ($commande->getLignes() as $ligne) {
                    $piece = $ligne->getPiece();
                    $quantiteCommandee = $ligne->getQuantite();

                    // On additionne la quantité reçue au stock actuel de la pièce
                    $nouveauStock = $piece->getQuantiteStock() + $quantiteCommandee;
                    $piece->setQuantiteStock($nouveauStock);
                }

                $this->addFlash('success', 'Commande marquée comme livrée ! Le stock des pièces a été mis à jour automatiquement.');
            } else {
                // Si on a juste modifié autre chose (ex: la date prévue)
                $this->addFlash('success', 'Les dates de la commande ont été mises à jour.');
            }

            // On sauvegarde tout en base de données (Commande + Lignes + Pièces mises à jour)
            $em->flush();

            // Redirection AJAX vers l'index
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'redirect' => $this->generateUrl('achats_commande_index'),
                ]);
            }

            // Fallback classique
            return $this->redirectToRoute('achats_commande_index');
        }

        return $this->render('achats/commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'achats_commande_delete', methods: ['POST'])]
    public function delete(Request $request, CommandeAchat $commande, EntityManagerInterface $em): Response
    {
        // SECURITY CHECK: Ensure the order is not delivered and has no lines.
        if ($commande->getDateReelle() !== null || $commande->getLignes()->count() > 0) {
            $this->addFlash('danger', 'Action impossible : Cette commande contient des pièces ou a déjà été livrée.');
            return $this->redirectToRoute('achats_commande_show', ['id' => $commande->getId()]);
        }

        if ($this->isCsrfTokenValid('delete_commande_'.$commande->getId(), $request->request->get('_token'))) {
            $em->remove($commande);
            $em->flush();
            $this->addFlash('success', 'La commande a été supprimée.');
            // Redirect to index after successful deletion of the entire order
            return $this->redirectToRoute('achats_commande_index');
        }

        $this->addFlash('danger', 'Action non autorisée (Token invalide).');
        return $this->redirectToRoute('achats_commande_show', ['id' => $commande->getId()]);
    }

    #[Route('/{id}/add-ligne', name: 'achats_commande_add_ligne', methods: ['GET', 'POST'])]
    public function addLigne(Request $request, CommandeAchat $commande, EntityManagerInterface $em): Response
    {
        $ligne = new CmdAchatLigne();
        $ligne->setCommandeAchat($commande);

        // On passe le fournisseur de la commande au formulaire pour filtrer la liste déroulante
        $form = $this->createForm(\App\Form\CmdAchatLigneType::class, $ligne, [
            'fournisseur' => $commande->getFournisseur(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ON A SUPPRIMÉ LA MISE À JOUR DE LA PIÈCE ICI.
            // Le prix saisi dans le formulaire est automatiquement sauvegardé


            $em->persist($ligne);
            $em->flush();

            $this->addFlash('success', 'La pièce a été ajoutée à la commande au prix négocié.');

            // Redirection AJAX vers la page show
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'redirect' => $this->generateUrl('achats_commande_show', ['id' => $commande->getId()]),
                ]);
            }

            return $this->redirectToRoute('achats_commande_show', ['id' => $commande->getId()]);
        }

        return $this->render('achats/commande/add_ligne.html.twig', [
            'commande' => $commande,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/ligne/{id}/delete', name: 'achats_commande_ligne_delete', methods: ['POST'])]
    public function deleteLigne(Request $request, CmdAchatLigne $ligne, EntityManagerInterface $em): Response
    {
        $commandeId = $ligne->getCommandeAchat()->getId();

        if ($this->isCsrfTokenValid('delete_ligne_'.$ligne->getId(), $request->request->get('_token'))) {
            $em->remove($ligne);
            $em->flush();
            $this->addFlash('success', 'La pièce a été retirée de la commande.');
        }

        return $this->redirectToRoute('achats_commande_show', ['id' => $commandeId]);
    }


}
