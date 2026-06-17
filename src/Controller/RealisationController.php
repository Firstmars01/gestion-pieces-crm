<?php

namespace App\Controller;

use App\Entity\Realisation;
use App\Entity\RealisationPoste;
use App\Form\RealisationType;
use App\Repository\RealisationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

            $this->addFlash('success', 'L\'ordre de fabrication a été lancé et ses étapes ont été générées.');

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

        if (null === $pointage->getTemps() && $pointage->getOperation()) {
            $pointage->setTemps($pointage->getTempsPrevu());
            $pointage->setPosteMachine($pointage->getOperation()->getPosteMachine());
        }

        $form = $this->createForm(\App\Form\RealisationPosteType::class, $pointage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le pointage a été enregistré avec succès.');

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
