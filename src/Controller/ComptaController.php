<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeAchat;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/compta')]
class ComptaController extends AbstractController
{
    // =========================================================================
    // PARTIE VENTES (CLIENTS)
    // =========================================================================

    #[Route('/ventes', name: 'compta_ventes_index', methods: ['GET'])]
    public function indexVentes(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        // On récupère uniquement les commandes qui sont expédiées/livrées
        $queryBuilder = $em->getRepository(Commande::class)->createQueryBuilder('c')
            ->leftJoin('c.client', 'client')->addSelect('client')
            ->where('c.isLivree = :livree')
            ->setParameter('livree', true)
            ->orderBy('c.dateFacture', 'DESC');

        // Barre de recherche
        if ($recherche = $request->query->get('q')) {
            $queryBuilder->andWhere('
                LOWER(client.raisonSociale) LIKE LOWER(:recherche)
                OR LOWER(client.nom) LIKE LOWER(:recherche)
                OR LOWER(client.prenom) LIKE LOWER(:recherche)
                OR LOWER(c.numero) LIKE LOWER(:recherche)
            ')
                ->setParameter('recherche', '%'.$recherche.'%');
        }

        $ventes = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('compta/ventes/index.html.twig', [
            'ventes' => $ventes,
        ]);
    }

    #[Route('/ventes/{id}/pdf', name: 'compta_vente_pdf', methods: ['GET'])]
    public function genererVentePdf(Commande $commande): Response
    {
        if (!$commande->isLivree()) {
            $this->addFlash('warning', 'Cette commande n\'est pas encore facturable.');

            return $this->redirectToRoute('compta_ventes_index');
        }

        $publicDirectory = $this->getParameter('kernel.project_dir').'/public';

        // 1. Configuration de Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);
        $pdfOptions->set('chroot', $publicDirectory);

        $dompdf = new Dompdf($pdfOptions);

        // 2. Génération du HTML
        $html = $this->renderView('compta/ventes/facture_pdf.html.twig', [
            'commande' => $commande,
            'public_dir' => $publicDirectory,
        ]);

        // 3. Intégration du HTML
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        // 4. Rendu du PDF
        $dompdf->render();

        // 5. Envoi
        $filename = 'Facture_Vente_'.$commande->getNumero().'.pdf';

        $dompdf->stream($filename, [
            'Attachment' => true,
        ]);

        return new Response('', 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    // =========================================================================
    // PARTIE ACHATS (FOURNISSEURS)
    // =========================================================================

    #[Route('/achats-fournisseurs', name: 'compta_achats_index', methods: ['GET'])]
    public function indexAchats(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        // On récupère les commandes d'achat qui ont été livrées (dateReelle non nulle)
        $queryBuilder = $em->getRepository(CommandeAchat::class)->createQueryBuilder('c')
            ->leftJoin('c.fournisseur', 'fournisseur')->addSelect('fournisseur')
            ->where('c.dateReelle IS NOT NULL')
            ->orderBy('c.dateReelle', 'DESC');

        // Barre de recherche (par ID de commande ou raison sociale du fournisseur)
        if ($recherche = $request->query->get('q')) {
            $queryBuilder->andWhere('LOWER(fournisseur.raisonSociale) LIKE LOWER(:recherche) OR c.id = :idRecherche')
                ->setParameter('recherche', '%'.$recherche.'%')
                ->setParameter('idRecherche', is_numeric($recherche) ? $recherche : 0);
        }

        $achats = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('compta/achats/index.html.twig', [
            'achats' => $achats,
        ]);
    }

    #[Route('/achats/facture/{id}/pdf', name: 'compta_achat_pdf', methods: ['GET'])]
    public function genererAchatPdf(CommandeAchat $commande): Response
    {
        if (null === $commande->getDateReelle()) {
            $this->addFlash('warning', 'Cette commande n\'est pas encore réceptionnée.');

            return $this->redirectToRoute('compta_achats_index');
        }

        $publicDirectory = $this->getParameter('kernel.project_dir').'/public';

        // 1. Configuration de Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);
        $pdfOptions->set('chroot', $publicDirectory);

        $dompdf = new Dompdf($pdfOptions);

        // 2. Génération du HTML (⚠️ Pense à créer ce template PDF s'il n'existe pas encore)
        $html = $this->renderView('compta/achats/facture_pdf.html.twig', [
            'commande' => $commande,
            'public_dir' => $publicDirectory,
        ]);

        // 3. Intégration du HTML
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        // 4. Rendu du PDF
        $dompdf->render();

        // 5. Envoi
        $filename = 'Facture_Achat_'.$commande->getId().'.pdf';

        $dompdf->stream($filename, [
            'Attachment' => true,
        ]);

        return new Response('', 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
