<?php

namespace App\Controller;

use App\Form\StatOpportunityFormType;
use App\Repository\OpportunityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        $data = $opportunityRepository->findChartStat();
        $opportunitiesChart = $this->createDateChart('Opportunités', $data);
        $statOpportunityForm = $this->createForm(StatOpportunityFormType::class, null, [
            'action' => $this->generateUrl('statistic_opportunity_filter'),
        ]);

        return $this->render('statistic/index.html.twig', [
            'opportunitiesChart' => $opportunitiesChart,
            'statOpportunityForm' => $statOpportunityForm->createView()
        ]);
    }

    /**
     * @Route("/opportunity/filter", name="statistic_opportunity_filter", methods={"POST"})
     */
    public function opportunityFilter(Request $request, OpportunityRepository $opportunityRepository): Response
    {
        $statOpportunityForm = $this->createForm(StatOpportunityFormType::class);
        $data = json_decode($request->getContent(), true);
        $statOpportunityForm->submit($data, false);
        $response = [];

        if ($statOpportunityForm->isSubmitted() && $statOpportunityForm->isValid()) {
            $formData = $statOpportunityForm->getData();
            $chartData = $opportunityRepository->findChartStat($formData['display'], $formData['dateStart'], $formData['dateEnd']);
            $opportunitiesChart = $this->createDateChart('Opportunités', $chartData, $formData['display']);
            $response = $this->render('statistic/_chart.html.twig', ['chart' => $opportunitiesChart]);
        }

        return $this->json($response, Response::HTTP_OK);
    }


    private function createDateChart(string $label, array $data, ?string $unit = 'day'): Chart
    {
        $displayFormats = [
            'year' => 'YYYY',
            'month' => 'MMM YYYY',
            'day' => 'DD/MM/YYYY'
        ];
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

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'datasets' => $datasets
        ]);

        $chart->setOptions([
            'responsive' => true,
            'aspectRatio' => 1.5,
            'scales' => [
                'x' => [
                    'type' => 'time',
                    'time' => [
                        'unit' => $unit,
                        'isoWeekday' => true,
                        'tooltipFormat' => $displayFormats[$unit],
                        'displayFormats' => $displayFormats,
                    ]
                ],
                'y' => ['beginAtZero' => true],
            ],
            'interaction' => [
                'intersect' => false,
            ],
            'plugins' => [
                'tooltip' => ['enable' => true],
                'legend' => ['display' => false],
                'zoom' => [
                    'pan' => [
                        'enabled' => true,
                        'mode' => 'x',
                        'speed' => 20,
                    ],
                    'zoom' => [
                        'wheel' => ['enabled' => true],
                        'pinch' => ['enabled' => true],
                        'mode' => 'x',
                        'sensitivity' => 3,
                    ]
                ],
            ]
        ]);

        return $chart;
    }
}
