<?php

namespace App\Controller;

use App\Form\StatOpportunityFormType;
use App\Repository\BalanceRepository;
use App\Repository\LogRepository;
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
    public function index(OpportunityRepository $opportunityRepository, BalanceRepository $balanceRepository, LogRepository $logRepository): Response
    {
        $assetsChart = $this->createDoughnutChart($balanceRepository->findChartStat());
        $balancesChart = $this->createLineChart($logRepository->findChartStat('Balance'));
        $opportunitiesChart = $this->createDateChart('Opportunités', $opportunityRepository->findChartStat());
        $statOpportunityForm = $this->createForm(StatOpportunityFormType::class, null, [
            'action' => $this->generateUrl('statistic_opportunity_filter'),
        ]);

        return $this->render('statistic/index.html.twig', [
            'assetsChart' => $assetsChart,
            'balancesChart' => $balancesChart,
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
        $color = RandomColor::randomOne();

        $datasets = [];
        foreach ($data as $element) {
            $datasets[] = [
                'label' => $label,
                'backgroundColor' => $color,
                'data' => [$element],
            ];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'datasets' => $datasets
        ]);

        $chart->setOptions([
            'responsive' => true,
            'aspectRatio' => 0.7,
            'maintainAspectRatio' => false,
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
                    /*'zoom' => [
                        'wheel' => ['enabled' => true],
                        'pinch' => ['enabled' => true],
                        'mode' => 'x',
                        'sensitivity' => 3,
                    ]*/
                ],
            ]
        ]);

        return $chart;
    }

    private function createDoughnutChart(array $data): Chart
    {
        $colors = RandomColor::randomPair(2);

        $labels = [];
        $seriesData = [];
        foreach ($data as $key => $value) {
            $labels[] = $key;
            $seriesData[] = $value;
        }

        $total = array_reduce($seriesData, function($a, $v) {
            return $a + $v;
        });
        $inPercent = array_map(function($v) use ($total) {
            return $v > 0 ? max($v / $total * 100, 1) : 0;
        }, $seriesData);


        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => null,
                    'backgroundColor' => $colors,
                    'data' => $inPercent,
                    'rawData' => $seriesData,
                    'borderWidth' => 1
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'aspectRatio' => 0.7,
            'maintainAspectRatio' => false,
            'spacing' => 1,
            'plugins' => [
                'tooltip' => ['enable' => true],
            ],
        ]);

        return $chart;
    }

    private function createLineChart(array $data): Chart
    {
        $labels = [];
        $datasets = [];
        foreach ($data as $currency => $dataset) {
            $datas = [];
            $color = RandomColor::randomOne();
            $label = $currency;

            foreach ($dataset as $data) {
                $labels[] = $data['label'];
                $datas[] = $data['data'];
            }
            $datasets[] = [
                'label' => $label,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'data' => $datas,
            ];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => $datasets
        ]);

        $chart->setOptions([
            'responsive' => true,
            'aspectRatio' => 0.7,
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => [
                    'type' => 'time',
                    'time' => [
                        'unit' => 'day',
                        'isoWeekday' => true,
                        'tooltipFormat' => 'DD/MM/YYYY H:mm:ss',
                    ]
                ],
                'y' => [
                    'type' => 'logarithmic'
                ]
            ],
            'interaction' => [
                'intersect' => false,
            ],
            'plugins' => [
                'tooltip' => ['enable' => true],
                'zoom' => [
                    'pan' => [
                        'enabled' => true,
                        'mode' => 'x',
                        'speed' => 20,
                    ],
                ],
            ],
        ]);

        return $chart;
    }
}
