<?php

namespace App\Controller;

use App\Entity\Realisation;
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
            ->orderBy('r.id', 'DESC'); // Les plus récentes en premier

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
            $entityManager->flush();

            $this->addFlash('success', 'L\'ordre de fabrication a été lancé.');

            // Une fois créé, on redirige l'ouvrier directement sur la page de pointage de ce lot !
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
        // C'est ici que l'ouvrier viendra pointer ses heures plus tard !
        return $this->render('atelier/realisation/show.html.twig', [
            'realisation' => $realisation,
        ]);
    }

    #[Route('/{id}/pointer/{operation_id}', name: 'atelier_realisation_pointer', methods: ['GET', 'POST'])]
    public function pointer(
        Realisation $realisation,
        int $operation_id,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // On récupère l'étape théorique de la gamme
        $gammeOperation = $entityManager->getRepository(\App\Entity\GammeOperation::class)->find($operation_id);

        if (!$gammeOperation) {
            throw $this->createNotFoundException('Opération introuvable.');
        }

        $pointage = new \App\Entity\RealisationPoste();
        $pointage->setRealisation($realisation);
        $pointage->setGammeOperation($gammeOperation);

        // On pré-remplit avec la théorie !
        if ($operation = $gammeOperation->getOperation()) {
            $pointage->setTemps($operation->getTempsPrevu());
            $pointage->setPosteMachine($operation->getPosteMachine());
        }

        $form = $this->createForm(\App\Form\RealisationPosteType::class, $pointage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pointage);
            $entityManager->flush();

            $this->addFlash('success', 'Le pointage a été enregistré avec succès.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_realisation_show', ['id' => $realisation->getId()])]);
            }
            return $this->redirectToRoute('atelier_realisation_show', ['id' => $realisation->getId()]);
        }

        return $this->render('atelier/realisation/pointage_new.html.twig', [
            'form' => $form->createView(),
            'gammeOperation' => $gammeOperation,
        ]);
    }
}
