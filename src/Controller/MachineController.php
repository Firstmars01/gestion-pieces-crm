<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Form\MachineType;
use App\Repository\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/atelier/machines')]
class MachineController extends AbstractController
{
    #[Route('/', name: 'atelier_machine_index', methods: ['GET'])]
    public function index(Request $request, MachineRepository $machineRepository, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $machineRepository->createQueryBuilder('m');
        $recherche = $request->query->get('q');

        if ($recherche) {
            $queryBuilder->andWhere('LOWER(m.libelle) LIKE LOWER(:recherche)')
                ->setParameter('recherche', '%' . $recherche . '%');
        }

        $machines = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('atelier/machine/index.html.twig', [
            'machines' => $machines,
        ]);
    }

    #[Route('/new', name: 'atelier_machine_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $machine = new Machine();
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($machine);
            $entityManager->flush();

            $this->addFlash('success', 'La machine a été ajoutée.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_machine_index')]);
            }
            return $this->redirectToRoute('atelier_machine_index');
        }

        return $this->render('atelier/machine/new.html.twig', [
            'form' => $form->createView(),
            'machine' => $machine,
        ]);
    }

    #[Route('/{id}/edit', name: 'atelier_machine_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Machine $machine, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La machine a été modifiée.');

            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_machine_index')]);
            }
            return $this->redirectToRoute('atelier_machine_index');
        }

        return $this->render('atelier/machine/new.html.twig', [
            'form' => $form->createView(),
            'machine' => $machine,
        ]);
    }

    #[Route('/{id}', name: 'atelier_machine_delete', methods: ['POST'])]
    public function delete(Request $request, Machine $machine, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_machine_' . $machine->getId(), $request->request->get('_token'))) {
            $entityManager->remove($machine);
            $entityManager->flush();
            $this->addFlash('success', 'La machine a été supprimée.');
        }

        return $this->redirectToRoute('atelier_machine_index');
    }
}
