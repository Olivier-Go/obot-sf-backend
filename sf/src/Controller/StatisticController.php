<?php

namespace App\Controller;

use App\Form\StatOpportunityFormType;
use App\Repository\OpportunityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use App\Utils\RandomColor;

/**
 * @Route("/statistic")
 */
class StatisticController extends AbstractController
{
    private ChartBuilderInterface $chartBuilder;

    public function __construct(ChartBuilderInterface $chartBuilder)
    {
        $this->chartBuilder = $chartBuilder;
    }

    /**
     * @Route("/", name="statistic_index")
     */
    public function index(OpportunityRepository $opportunityRepository): Response
    {
        $data = $opportunityRepository->findChartStatByDay();
        $opportunitiesChart = $this->createOpportunityChart('Opportunités', $data);
        $statOpportunityForm = $this->createForm(StatOpportunityFormType::class);

        return $this->render('statistic/index.html.twig', [
            'opportunitiesChart' => $opportunitiesChart,
            'statOpportunityForm' => $statOpportunityForm->createView()
        ]);
    }

    private function createOpportunityChart(string $label, array $data): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $color = RandomColor::one([
            'luminosity' => 'light',
            'hue' => 'purple',
            'format' => 'hslCss'
        ]);

        $datasets = [];
        foreach ($data as $element) {
            $datasets[] = [
                'label' => $label,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'data' => [$element],
            ];
        }

        $chart->setData([
            'datasets' => $datasets
        ]);

        $chart->setOptions([
            'responsive' => true,
            'locale' => 'fr-FR',
            'scales' => [
                'x' => [
                    'type' => 'time',
                    'time' => [
                        'unit' => 'day',
                        'tooltipFormat' => 'dd/MM/yyyy',
                        'displayFormats' => ['day' => 'dd/MM/yyyy'],
                    ],
                ],
                'y' => ['beginAtZero' => true]
            ],
            'plugins' => [
                'tooltip' => ['enable' => true],
                'legend' => ['display' => false]
            ]
        ]);

        return $chart;
    }
}
