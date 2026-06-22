<?php

namespace App\Controller;

use App\Entity\Gamme;
use App\Form\GammeType;
use App\Repository\GammeRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
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
        if ($this->isCsrfTokenValid('delete_gamme_'.$gamme->getId(), $request->request->get('_token'))) {
            try {
                // 1. SUPPRIMER LES ÉTAPES (C'est ça qui empêchait la suppression !)
                foreach ($gamme->getGammeOperations() as $etape) {
                    $entityManager->remove($etape);
                }

                // 2. SUPPRIMER LA GAMME
                // Doctrine va automatiquement libérer la pièce liée lors du flush
                $entityManager->remove($gamme);
                $entityManager->flush();

                $this->addFlash('success', 'La gamme a été supprimée avec succès. La pièce est de nouveau libre !');
            } catch (\Exception $e) {
                // Si la suppression est bloquée par un ordre de fabrication existant
                $this->addFlash('danger', 'Impossible de supprimer cette gamme, elle est probablement liée à un historique de fabrication.');
            }
        } else {
            $this->addFlash('danger', 'Action non autorisée (Token CSRF invalide).');
        }

        return $this->redirectToRoute('atelier_gamme_index');
    }

    #[Route('/{id}/etapes', name: 'atelier_gamme_show', methods: ['GET'])]
    public function show(Gamme $gamme): Response
    {
        return $this->render('atelier/gamme/show.html.twig', [
            'gamme' => $gamme,
        ]);
    }

    #[Route('/{id}/etape/nouvelle', name: 'atelier_gamme_etape_new', methods: ['GET', 'POST'])]
    public function newEtape(Request $request, Gamme $gamme, EntityManagerInterface $entityManager): Response
    {
        $gammeOperation = new \App\Entity\GammeOperation();
        $gammeOperation->setGamme($gamme);

        $nextOrdre = count($gamme->getGammeOperations()) + 1;
        $gammeOperation->setOrdre($nextOrdre);

        $form = $this->createForm(\App\Form\GammeOperationType::class, $gammeOperation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($gammeOperation);
            $entityManager->flush();

            $this->addFlash('success', 'L\'étape a bien été ajoutée à la gamme.');

            // On redirige vers la page de détails de la gamme
            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_gamme_show', ['id' => $gamme->getId()])]);
            }

            return $this->redirectToRoute('atelier_gamme_show', ['id' => $gamme->getId()]);
        }

        return $this->render('atelier/gamme/_form_etape.html.twig', [
            'form' => $form->createView(),
            'gamme' => $gamme,
            'gammeOperation' => $gammeOperation,
        ]);
    }

    #[Route('/etape/{id}/modifier', name: 'atelier_gamme_etape_edit', methods: ['GET', 'POST'])]
    public function editEtape(Request $request, \App\Entity\GammeOperation $gammeOperation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(\App\Form\GammeOperationType::class, $gammeOperation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'L\'étape a bien été modifiée.');

            // On retourne sur la page de la gamme après modification
            if ($request->isXmlHttpRequest()) {
                return $this->json(['redirect' => $this->generateUrl('atelier_gamme_show', ['id' => $gammeOperation->getGamme()->getId()])]);
            }

            return $this->redirectToRoute('atelier_gamme_show', ['id' => $gammeOperation->getGamme()->getId()]);
        }

        return $this->render('atelier/gamme/_form_etape.html.twig', [
            'form' => $form->createView(),
            'gammeOperation' => $gammeOperation,
            'gamme' => $gammeOperation->getGamme(),
        ]);
    }

    #[Route('/etape/{id}/supprimer', name: 'atelier_gamme_etape_delete', methods: ['POST'])]
    public function deleteEtape(Request $request, \App\Entity\GammeOperation $gammeOperation, EntityManagerInterface $entityManager): Response
    {
        // On récupère l'objet Gamme avant de supprimer l'étape
        $gamme = $gammeOperation->getGamme();
        $gammeId = $gamme->getId();

        if ($this->isCsrfTokenValid('delete_etape_'.$gammeOperation->getId(), $request->request->get('_token'))) {
            try {
                // 1. On supprime l'étape
                $entityManager->remove($gammeOperation);
                $entityManager->flush();

                if ($gamme) {
                    $gamme->recalculerOrdreOperations();
                    $entityManager->flush();
                }

                $this->addFlash('success', 'L\'étape a été retirée de la gamme et l\'ordre a été recalculé.');
            } catch (ForeignKeyConstraintViolationException $e) {
                // Si la base de données bloque la suppression à cause de l'historique (pour les vieilles données)
                $this->addFlash('danger', 'Impossible de supprimer cette étape : des pointages ont déjà été réalisés par l\'atelier sur cette opération.');
            }
        } else {
            $this->addFlash('danger', 'Action non autorisée (Token CSRF invalide).');
        }

        return $this->redirectToRoute('atelier_gamme_show', ['id' => $gammeId]);
    }
}
