<?php

namespace App\Controller;

use App\Entity\PosteTravail;
use App\Form\PosteTravailType;
use App\Repository\PosteTravailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/atelier/postes')]
class PosteTravailController extends AbstractController
{
    #[Route('/', name: 'atelier_poste_index', methods: ['GET'])]
    public function index(Request $request, PosteTravailRepository $posteRepository, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $posteRepository->createQueryBuilder('p')

            // 1. On joint les machines liées à ce poste
            ->leftJoin('p.posteMachines', 'pm')->addSelect('pm')
            ->leftJoin('pm.machine', 'm')->addSelect('m')

            // 2. On joint les qualifications (ouvriers) liées à ce poste
            ->leftJoin('p.qualifications', 'q')->addSelect('q')
            ->leftJoin('q.user', 'u')->addSelect('u')

            ->orderBy('p.id', 'ASC');

        $recherche = $request->query->get('q');

        if ($recherche) {
            // La recherche marchera parfaitement maintenant avec le bon alias 'p'
            $queryBuilder->andWhere('LOWER(p.libelle) LIKE LOWER(:recherche)')
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        $postes = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('atelier/poste_travail/index.html.twig', [
            'postes' => $postes,
        ]);
    }

    #[Route('/new', name: 'atelier_poste_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $poste = new PosteTravail();
        $form = $this->createForm(PosteTravailType::class, $poste);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($poste);
            $entityManager->flush();

            $this->addFlash('success', 'Le poste de travail a été ajouté.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_poste_index')]);
            }

            return $this->redirectToRoute('atelier_poste_index');
        }

        return $this->render('atelier/poste_travail/new.html.twig', [
            'form' => $form->createView(),
            'poste' => $poste,
        ]);
    }

    #[Route('/{id}/edit', name: 'atelier_poste_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PosteTravail $poste, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PosteTravailType::class, $poste);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le poste de travail a été modifié.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_poste_index')]);
            }

            return $this->redirectToRoute('atelier_poste_index');
        }

        return $this->render('atelier/poste_travail/new.html.twig', [
            'form' => $form->createView(),
            'poste' => $poste,
        ]);
    }

    #[Route('/{id}', name: 'atelier_poste_delete', methods: ['POST'])]
    public function delete(Request $request, PosteTravail $poste, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_poste_'.$poste->getId(), $request->request->get('_token'))) {
            $entityManager->remove($poste);
            $entityManager->flush();
            $this->addFlash('success', 'Le poste de travail a été supprimé.');
        }

        return $this->redirectToRoute('atelier_poste_index');
    }
}
