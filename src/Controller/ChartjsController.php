<?php

namespace App\Controller;

use App\Entity\Invoices;
use App\Repository\InvoicesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ChartjsController extends AbstractController
{
    #[Route('/chartjs', name: 'app_chartjs')]
//DailyResultRepository $dailyResultRepository,
    public function index(ChartBuilderInterface $chartBuilder, InvoicesRepository $invoices): Response
    {
        $revenues = $invoices->findAll();
        $monthlyData = [];

        foreach ($invoices as $invoice) {
            $month = $invoice->getCreatedAt()->format('F Y'); // Extrait le mois et l'année (par exemple, "January 2023")
            $amount = $invoice->getAmount();

            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = 0;
            }

            $monthlyData[$month] += $amount;
        }
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => array_keys($monthlyData),
            'datasets' => [
                [
                    'label' => 'Revenues',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                    'data' => array_values($monthlyData),
                ],
            ],
        ]);
        $chart->setOptions([
            'type' => 'bar',
            'responsive' => true,
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'ChartjsController',
            'chart' => $chart,
//            'offres' => $chartOffers,
        ]);
    }

}

