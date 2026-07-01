<?php

namespace App\Controller;

use App\Entity\Operation;
use App\Form\OperationType;
use App\Repository\OperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/atelier/operations')]
class OperationController extends AbstractController
{
    #[Route('/', name: 'atelier_operation_index', methods: ['GET'])]
    public function index(Request $request, OperationRepository $operationRepository, PaginatorInterface $paginator): Response
    {
        // On fait des jointures pour récupérer le poste et la machine associés en une seule fois
        $queryBuilder = $operationRepository->createQueryBuilder('o')
            ->leftJoin('o.posteMachine', 'pm')->addSelect('pm')
            ->leftJoin('pm.poste', 'p')->addSelect('p')
            ->leftJoin('pm.machine', 'm')->addSelect('m')
            ->orderBy('o.id', 'ASC');


        if ($recherche = trim((string) $request->query->get('q'))) {
            // On peut chercher par nom d'opération, nom de machine ou nom de poste !
            $queryBuilder->andWhere('LOWER(o.libelle) LIKE LOWER(:recherche) OR LOWER(p.libelle) LIKE LOWER(:recherche) OR LOWER(m.libelle) LIKE LOWER(:recherche)')
                ->setParameter('recherche', '%' . $recherche . '%');
        }

        $operations = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('atelier/operation/index.html.twig', [
            'operations' => $operations,
        ]);
    }

    #[Route('/new', name: 'atelier_operation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $operation = new Operation();
        $form = $this->createForm(OperationType::class, $operation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($operation);
            $entityManager->flush();

            $this->addFlash('success', 'L\'opération a été ajoutée au catalogue.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_operation_index')]);
            }
            return $this->redirectToRoute('atelier_operation_index');
        }

        return $this->render('atelier/operation/new.html.twig', [
            'form' => $form->createView(),
            'operation' => $operation,
        ]);
    }

    #[Route('/{id}/edit', name: 'atelier_operation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Operation $operation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OperationType::class, $operation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'opération a été mise à jour.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_operation_index')]);
            }
            return $this->redirectToRoute('atelier_operation_index');
        }

        return $this->render('atelier/operation/new.html.twig', [
            'form' => $form->createView(),
            'operation' => $operation,
        ]);
    }

    #[Route('/{id}', name: 'atelier_operation_delete', methods: ['POST'])]
    public function delete(Request $request, Operation $operation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_operation_' . $operation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($operation);
            $entityManager->flush();
            $this->addFlash('success', 'L\'opération a été supprimée.');
        }

        return $this->redirectToRoute('atelier_operation_index');
    }
}
