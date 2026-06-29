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
        $queryBuilder = $em->getRepository(Commande::class)->createQueryBuilder('c')
            ->leftJoin('c.client', 'client')->addSelect('client')
            ->where('c.isLivree = :livree')
            ->setParameter('livree', true)
            ->orderBy('c.dateFacture', 'DESC');

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

        // --- NOUVEAU : Récupération des années disponibles pour les ventes ---
        $lignesDates = $em->getRepository(Commande::class)->createQueryBuilder('c')
            ->select('c.dateFacture')
            ->where('c.isLivree = true')
            ->andWhere('c.dateFacture IS NOT NULL')
            ->getQuery()
            ->getResult();

        $anneesDispo = [];
        foreach ($lignesDates as $ligne) {
            if (isset($ligne['dateFacture']) && $ligne['dateFacture'] instanceof \DateTimeInterface) {
                $anneesDispo[$ligne['dateFacture']->format('Y')] = $ligne['dateFacture']->format('Y');
            }
        }
        rsort($anneesDispo); // Tri décroissant (2026, 2025...)

        // Valeur par défaut si la base est vide
        if (empty($anneesDispo)) {
            $anneesDispo[] = date('Y');
        }

        return $this->render('compta/ventes/index.html.twig', [
            'ventes' => $ventes,
            'anneesDispo' => $anneesDispo,
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
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);
        $pdfOptions->set('chroot', $publicDirectory);

        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('compta/ventes/facture_pdf.html.twig', [
            'commande' => $commande,
            'public_dir' => $publicDirectory,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('Facture_Vente_'.$commande->getNumero().'.pdf', ['Attachment' => true]);

        return new Response('', 200, ['Content-Type' => 'application/pdf']);
    }

    #[Route('/ventes/export-csv', name: 'compta_ventes_csv', methods: ['GET'])]
    public function exportVentesCsv(Request $request, EntityManagerInterface $em): Response
    {
        // --- NOUVEAU : Récupération depuis les listes déroulantes ---
        $mois = $request->query->get('mois');
        $annee = $request->query->get('annee');
        $period = ($mois && $annee) ? sprintf('%04d-%02d', $annee, $mois) : (new \DateTime('first day of last month'))->format('Y-m');

        $startDate = new \DateTime($period.'-01 00:00:00');
        $endDate = (clone $startDate)->modify('last day of this month 23:59:59');

        $commandes = $em->getRepository(Commande::class)->createQueryBuilder('c')
            ->leftJoin('c.client', 'client')->addSelect('client')
            ->where('c.isLivree = :livree')
            ->andWhere('c.dateFacture BETWEEN :debut AND :fin')
            ->setParameter('livree', true)
            ->setParameter('debut', $startDate)
            ->setParameter('fin', $endDate)
            ->orderBy('c.dateFacture', 'ASC')
            ->getQuery()
            ->getResult();

        $csvContent = "N Facture;Date;Client;Nb Lignes;Montant HT\n";

        foreach ($commandes as $cmd) {
            $client = $cmd->getClient()->getRaisonSociale() ?: $cmd->getClient()->getNomComplet();
            $date = $cmd->getDateFacture() ? $cmd->getDateFacture()->format('d/m/Y') : '';
            $nbLignes = count($cmd->getCommandeLignes());
            $montant = number_format($cmd->getTotal(), 2, ',', '');

            $csvContent .= sprintf("%s;%s;\"%s\";%d;%s\n",
                $cmd->getNumero(), $date, str_replace('"', '""', $client), $nbLignes, $montant
            );
        }

        return new Response("\xEF\xBB\xBF".$csvContent, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="Export_Ventes_'.$period.'.csv"',
        ]);
    }

    // =========================================================================
    // PARTIE ACHATS (FOURNISSEURS)
    // =========================================================================

    #[Route('/achats-fournisseurs', name: 'compta_achats_index', methods: ['GET'])]
    public function indexAchats(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $em->getRepository(CommandeAchat::class)->createQueryBuilder('c')
            ->leftJoin('c.fournisseur', 'fournisseur')->addSelect('fournisseur')
            ->where('c.dateReelle IS NOT NULL')
            ->orderBy('c.dateReelle', 'DESC');

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

        // --- NOUVEAU : Récupération des années disponibles pour les achats ---
        $lignesDates = $em->getRepository(CommandeAchat::class)->createQueryBuilder('c')
            ->select('c.dateReelle')
            ->where('c.dateReelle IS NOT NULL')
            ->getQuery()
            ->getResult();

        $anneesDispo = [];
        foreach ($lignesDates as $ligne) {
            if (isset($ligne['dateReelle']) && $ligne['dateReelle'] instanceof \DateTimeInterface) {
                $anneesDispo[$ligne['dateReelle']->format('Y')] = $ligne['dateReelle']->format('Y');
            }
        }
        rsort($anneesDispo);

        if (empty($anneesDispo)) {
            $anneesDispo[] = date('Y');
        }

        return $this->render('compta/achats/index.html.twig', [
            'achats' => $achats,
            'anneesDispo' => $anneesDispo,
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
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->setIsRemoteEnabled(true);
        $pdfOptions->set('chroot', $publicDirectory);

        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('compta/achats/facture_pdf.html.twig', [
            'commande' => $commande,
            'public_dir' => $publicDirectory,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('Facture_Achat_'.$commande->getId().'.pdf', ['Attachment' => true]);

        return new Response('', 200, ['Content-Type' => 'application/pdf']);
    }

    #[Route('/achats/export-csv', name: 'compta_achats_csv', methods: ['GET'])]
    public function exportAchatsCsv(Request $request, EntityManagerInterface $em): Response
    {
        // --- NOUVEAU : Récupération depuis les listes déroulantes ---
        $mois = $request->query->get('mois');
        $annee = $request->query->get('annee');
        $period = ($mois && $annee) ? sprintf('%04d-%02d', $annee, $mois) : (new \DateTime('first day of last month'))->format('Y-m');

        $startDate = new \DateTime($period.'-01 00:00:00');
        $endDate = clone $startDate;
        $endDate->modify('last day of this month 23:59:59');

        $achats = $em->getRepository(CommandeAchat::class)->createQueryBuilder('c')
            ->leftJoin('c.fournisseur', 'fournisseur')->addSelect('fournisseur')
            ->where('c.dateReelle BETWEEN :debut AND :fin')
            ->setParameter('debut', $startDate)
            ->setParameter('fin', $endDate)
            ->orderBy('c.dateReelle', 'ASC')
            ->getQuery()
            ->getResult();

        $csvContent = "N Commande;Date Reception;Fournisseur;Nb Lignes;Montant HT\n";

        foreach ($achats as $achat) {
            $fournisseur = $achat->getFournisseur()->getRaisonSociale();
            $date = $achat->getDateReelle() ? $achat->getDateReelle()->format('d/m/Y') : '';
            $nbLignes = count($achat->getLignes());
            $montant = number_format($achat->getTotal(), 2, ',', '');

            $csvContent .= sprintf("%s;%s;\"%s\";%d;%s\n",
                $achat->getId(), $date, str_replace('"', '""', $fournisseur), $nbLignes, $montant
            );
        }

        return new Response("\xEF\xBB\xBF".$csvContent, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="Export_Achats_a_payer_'.$period.'.csv"',
        ]);
    }
}
