<?php

namespace App\Controller;

use App\Entity\Qualification;
use App\Form\QualificationType;
use App\Repository\QualificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/atelier/qualifications')]
class QualificationController extends AbstractController
{
    #[Route('/', name: 'atelier_qualification_index', methods: ['GET'])]
    public function index(Request $request, QualificationRepository $qualificationRepository, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $qualificationRepository->createQueryBuilder('q')
            ->leftJoin('q.user', 'u')->addSelect('u')
            ->leftJoin('q.poste', 'p')->addSelect('p')
            ->orderBy('q.id', 'DESC'); // On affiche les plus récentes en premier

        $recherche = $request->query->get('recherche');

        if ($recherche) {
            $queryBuilder->andWhere('LOWER(u.nom) LIKE LOWER(:recherche) OR LOWER(u.prenom) LIKE LOWER(:recherche) OR LOWER(p.libelle) LIKE LOWER(:recherche)')
                ->setParameter('recherche', '%' . $recherche . '%');
        }

        $qualifications = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('atelier/qualification/index.html.twig', [
            'qualifications' => $qualifications,
        ]);
    }

    #[Route('/new', name: 'atelier_qualification_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $qualification = new Qualification();
        $form = $this->createForm(QualificationType::class, $qualification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Optionnel : Vérifier si l'utilisateur a déjà cette qualification pour éviter les doublons
            $exists = $entityManager->getRepository(Qualification::class)->findOneBy([
                'user' => $qualification->getUser(),
                'poste' => $qualification->getPoste()
            ]);

            if ($exists) {
                $this->addFlash('danger', 'Cet ouvrier est déjà qualifié sur ce poste !');
            } else {
                $entityManager->persist($qualification);
                $entityManager->flush();
                $this->addFlash('success', 'La qualification a été ajoutée.');
            }

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_qualification_index')]);
            }
            return $this->redirectToRoute('atelier_qualification_index');
        }

        return $this->render('atelier/qualification/new.html.twig', [
            'form' => $form->createView(),
            'qualification' => $qualification,
        ]);
    }

    #[Route('/{id}/edit', name: 'atelier_qualification_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Qualification $qualification, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QualificationType::class, $qualification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'La qualification a été modifiée.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_qualification_index')]);
            }
            return $this->redirectToRoute('atelier_qualification_index');
        }

        return $this->render('atelier/qualification/new.html.twig', [
            'form' => $form->createView(),
            'qualification' => $qualification,
        ]);
    }

    #[Route('/{id}', name: 'atelier_qualification_delete', methods: ['POST'])]
    public function delete(Request $request, Qualification $qualification, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_qualification_' . $qualification->getId(), $request->request->get('_token'))) {
            $entityManager->remove($qualification);
            $entityManager->flush();
            $this->addFlash('success', 'La qualification a été retirée.');
        }

        return $this->redirectToRoute('atelier_qualification_index');
    }
}
