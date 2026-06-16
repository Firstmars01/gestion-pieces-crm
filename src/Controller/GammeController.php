<?php

namespace App\Controller;

use App\Entity\Gamme;
use App\Form\GammeType;
use App\Repository\GammeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/atelier/gammes')]
class GammeController extends AbstractController
{
    #[Route('/', name: 'atelier_gamme_index', methods: ['GET'])]
    public function index(Request $request, GammeRepository $gammeRepository, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $gammeRepository->createQueryBuilder('g')
            ->leftJoin('g.piece', 'p')
            ->addSelect('p')
            ->leftJoin('g.user', 'u')
            ->addSelect('u')
            ->orderBy('g.id', 'ASC'); // Tri stable par défaut pour la pagination

        $recherche = $request->query->get('q');

        if ($recherche) {
            $queryBuilder->andWhere('LOWER(g.libelle) LIKE LOWER(:recherche) OR LOWER(p.reference) LIKE LOWER(:recherche) OR LOWER(u.nom) LIKE LOWER(:recherche) OR LOWER(u.prenom) LIKE LOWER(:recherche)')
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        $gammes = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('atelier/gamme/index.html.twig', [
            'gammes' => $gammes,
        ]);
    }

    #[Route('/new', name: 'atelier_gamme_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $gamme = new Gamme();
        $form = $this->createForm(GammeType::class, $gamme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($gamme);
            $entityManager->flush();

            $this->addFlash('success', 'La gamme a été créée avec succès.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_gamme_index')]);
            }
            return $this->redirectToRoute('atelier_gamme_index');
        }

        return $this->render('atelier/gamme/new.html.twig', [
            'form' => $form->createView(),
            'gamme' => $gamme,
        ]);
    }

    #[Route('/{id}/edit', name: 'atelier_gamme_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Gamme $gamme, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GammeType::class, $gamme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La gamme a été mise à jour.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_gamme_index')]);
            }
            return $this->redirectToRoute('atelier_gamme_index');
        }

        return $this->render('atelier/gamme/new.html.twig', [
            'form' => $form->createView(),
            'gamme' => $gamme,
        ]);
    }

    #[Route('/{id}', name: 'atelier_gamme_delete', methods: ['POST'])]
    public function delete(Request $request, Gamme $gamme, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_gamme_' . $gamme->getId(), $request->request->get('_token'))) {
            $entityManager->remove($gamme);
            $entityManager->flush();
            $this->addFlash('success', 'La gamme a été supprimée.');
        } else {
            $this->addFlash('danger', 'Action non autorisée (Token CSRF invalide).');
        }

        return $this->redirectToRoute('atelier_gamme_index');
    }
}
