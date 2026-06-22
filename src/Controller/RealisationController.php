<?php

namespace App\Controller;

use App\Entity\Realisation;
use App\Entity\RealisationPoste;
use App\Form\RealisationType;
use App\Repository\RealisationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/atelier/realisations')]
class RealisationController extends AbstractController
{
    #[Route('/', name: 'atelier_realisation_index', methods: ['GET'])]
    public function index(Request $request, RealisationRepository $realisationRepository, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $realisationRepository->createQueryBuilder('r')
            ->leftJoin('r.gamme', 'g')->addSelect('g')
            ->leftJoin('g.piece', 'p')->addSelect('p')
            ->orderBy('r.id', 'DESC');

        $q = trim((string) $request->query->get('q', ''));

        if ('' !== $q) {
            $searchConditions = [
                'LOWER(g.libelle) LIKE LOWER(:q)',
                'LOWER(p.reference) LIKE LOWER(:q)',
                'LOWER(p.libelle) LIKE LOWER(:q)',
            ];

            if (ctype_digit($q)) {
                $searchConditions[] = 'r.id = :id';
            }

            $queryBuilder
                ->andWhere('('.implode(' OR ', $searchConditions).')')
                ->setParameter('q', '%'.$q.'%');

            if (ctype_digit($q)) {
                $queryBuilder->setParameter('id', (int) $q);
            }
        }

        $realisations = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('atelier/realisation/index.html.twig', [
            'realisations' => $realisations,
        ]);
    }

    #[Route('/nouvelle', name: 'atelier_realisation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $realisation = new Realisation();
        $form = $this->createForm(RealisationType::class, $realisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gamme = $realisation->getGamme();
            $pieceAProduire = $gamme ? $gamme->getPiece() : null;

            // 1. VÉRIFICATION ET DÉDUCTION DES STOCKS DE MATIÈRES PREMIÈRES
            if ($pieceAProduire) {
                $peutFabriquer = true;
                $manquants = [];

                // On boucle sur la composition (nomenclature) de la pièce
                foreach ($pieceAProduire->getComposants() as $composant) {
                    // On récupère la matière première et la quantité grâce à ton entité PieceComposition
                    $matiere = $composant->getPieceEnfant();
                    $quantiteNecessaire = $composant->getQuantite();

                    if ($matiere->getQuantiteStock() < $quantiteNecessaire) {
                        $peutFabriquer = false;
                        $manquants[] = $matiere->getLibelle().' (Requis : '.$quantiteNecessaire.', Stock : '.$matiere->getQuantiteStock().')';
                    }
                }

                // SI STOCK INSUFFISANT : On bloque et on affiche l'erreur dans la modale
                if (!$peutFabriquer) {
                    $form->addError(new FormError('Impossible de lancer la fabrication. Stock insuffisant : '.implode(' | ', $manquants)));

                    return $this->render('atelier/realisation/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                // SI TOUT EST BON : On déduit le stock
                foreach ($pieceAProduire->getComposants() as $composant) {
                    $matiere = $composant->getPieceEnfant();
                    $quantiteNecessaire = $composant->getQuantite();

                    $matiere->setQuantiteStock($matiere->getQuantiteStock() - $quantiteNecessaire);
                    // L'entité matière étant déjà gérée par Doctrine, le flush() final la mettra à jour en base.
                }
            }

            $entityManager->persist($realisation);

            if ($realisation->getGamme()) {
                // ARCHIVE DE LA GAMME ET DE LA PIÈCE
                $realisation->setGammeLibelleArchive($realisation->getGamme()->getLibelle());
                if ($realisation->getGamme()->getPiece()) {
                    $realisation->setPieceReferenceArchive($realisation->getGamme()->getPiece()->getReference());
                } else {
                    $realisation->setPieceReferenceArchive('Non définie');
                }

                foreach ($realisation->getGamme()->getGammeOperations() as $gammeOp) {
                    $etapeReelle = new RealisationPoste();
                    $etapeReelle->setRealisation($realisation);
                    $etapeReelle->setOperation($gammeOp->getOperation());
                    $etapeReelle->setOrdre($gammeOp->getOrdre());

                    if ($gammeOp->getOperation()) {
                        // ARCHIVE DU NOM DE L'OPÉRATION
                        $etapeReelle->setOperationLibelleArchive($gammeOp->getOperation()->getLibelle());
                        $etapeReelle->setTempsPrevu($gammeOp->getOperation()->getTempsPrevu());
                    }

                    $entityManager->persist($etapeReelle);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'L\'ordre de fabrication a été lancé et les matières premières ont été déduites du stock.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_realisation_show', ['id' => $realisation->getId()])]);
            }

            return $this->redirectToRoute('atelier_realisation_show', ['id' => $realisation->getId()]);
        }

        return $this->render('atelier/realisation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/pointage', name: 'atelier_realisation_show', methods: ['GET'])]
    public function show(Realisation $realisation): Response
    {
        return $this->render('atelier/realisation/show.html.twig', [
            'realisation' => $realisation,
        ]);
    }

    #[Route('/{id}/pointer/{poste_id}', name: 'atelier_realisation_pointer', methods: ['GET', 'POST'])]
    public function pointer(
        Realisation $realisation,
        int $poste_id,
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {
        $pointage = $entityManager->getRepository(RealisationPoste::class)->find($poste_id);

        if (!$pointage) {
            throw $this->createNotFoundException('Étape introuvable.');
        }

        // Si le temps est actuellement null, c'est que l'étape n'a ENCORE JAMAIS été pointée.
        $isFirstTimePointingThisStep = (null === $pointage->getTemps());

        // Pré-remplissage si c'est le premier pointage de cette étape (pour l'affichage du formulaire)
        if ($isFirstTimePointingThisStep && $pointage->getOperation()) {
            $pointage->setTemps($pointage->getTempsPrevu());
            $pointage->setPosteMachine($pointage->getOperation()->getPosteMachine());
        }

        $form = $this->createForm(\App\Form\RealisationPosteType::class, $pointage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1. On applique d'abord la validation du formulaire en base de données
            $entityManager->flush();

            // 2. On vérifie si TOUTES les étapes de l'Ordre de Fabrication sont désormais remplies
            $ofEstEntierementTermine = true;
            foreach ($realisation->getRealisationPostes() as $rp) {
                if (null === $rp->getTemps()) {
                    $ofEstEntierementTermine = false;
                    break;
                }
            }

            // 3. SI c'était le premier pointage de cette étape ET que tout l'OF est maintenant fini
            if ($isFirstTimePointingThisStep && $ofEstEntierementTermine) {
                if ($realisation->getGamme() && $realisation->getGamme()->getPiece()) {
                    $pieceFabriquee = $realisation->getGamme()->getPiece();

                    // Sécurité anti-null pour le stock actuel
                    $stockActuel = $pieceFabriquee->getQuantiteStock() ?? 0;
                    $pieceFabriquee->setQuantiteStock($stockActuel + 1);

                    // On re-flush pour envoyer la mise à jour du stock de la pièce en BDD !
                    $entityManager->flush();

                    $this->addFlash('success', 'Pointage enregistré. L\'Ordre de Fabrication est TERMINÉ ! 1 unité de "'.$pieceFabriquee->getLibelle().'" a été ajoutée au stock.');
                } else {
                    $this->addFlash('success', 'Toutes les étapes sont terminées (Aucune pièce liée à cette gamme).');
                }
            } else {
                $this->addFlash('success', 'Le pointage a été enregistré avec succès.');
            }

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_realisation_show', ['id' => $realisation->getId()])]);
            }

            return $this->redirectToRoute('atelier_realisation_show', ['id' => $realisation->getId()]);
        }

        return $this->render('atelier/realisation/pointage_new.html.twig', [
            'form' => $form->createView(),
            'gammeOperation' => clone $pointage,
        ]);
    }
}
