<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ChartjsController extends AbstractController
{
    #[Route('/chartjs', name: 'app_chartjs')]
//DailyResultRepository $dailyResultRepository,
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
//        $dailyResults = $dailyResultRepository->findAll();
        $labels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October','November','December'];
        $data = [0, 10, 5, 2, 20, 30, 45];
//        foreach ($dailyResults as $dailyResult) {
////            $data[] = $dailyResult->getDate()->format('Y-m-d');
//            $data[] = $dailyResult->getValue();
//        }
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(90, 99, 132)',
                    'borderColor' => 'rgb(25, 99, 132)',
                    'data' => $data,
                ],
            ],
        ]);
//        $chart->setOptions([
//            'scales' => [
//                'y' => [
//                    'suggestedMin' => 0,
//                    'suggestedMax' => 100,
//                ],
//            ],
//        ]);
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'ChartjsController',
            'chart' => $chart,
        ]);
    }
}
