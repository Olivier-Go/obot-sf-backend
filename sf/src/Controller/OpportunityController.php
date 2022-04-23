<?php

namespace App\Controller;

use App\Entity\Opportunity;
use App\Repository\OpportunityRepository;
use App\Service\ExportService;
use App\Service\OpportunityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/opportunity")
 */
class OpportunityController extends AbstractController
{
    private ExportService $exportService;
    private OpportunityService $opportunityService;

    public function __construct(ExportService $exportService, OpportunityService $opportunityService)
    {
        $this->exportService = $exportService;
        $this->opportunityService = $opportunityService;
    }

    /**
     * @Route("/", name="opportunity_index")
     */
    public function index(Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $maxItemPerPage = !empty($request->query->getInt('maxItemPerPage')) ? $request->query->getInt('maxItemPerPage') : 20;

        // Export
        $export = $request->get('export');
        if ($export === 'pdf') {
            $params['name'] = 'opportunites';
            $params['template'] = 'opportunity/index.html.twig';
            $params['pagination'] = $this->opportunityService->paginateOpportunities(
                1,
                0
            );
            return $this->exportService->exportpdf($params);
        }

        return $this->render('opportunity/index.html.twig', [
            'pagination' => $this->opportunityService->paginateOpportunities($page, $maxItemPerPage)
        ]);
    }

    /**
     * @Route("/log", name="opportunity_log", methods={"POST"})
     */
    public function log(Request $request, OpportunityRepository $opportunityRepository): Response
    {
        $data = $request->toArray();
        $response = [];

        if ($data['id']) {
            $opportunity = $opportunityRepository->find($data['id']);

            if ($opportunity instanceof Opportunity) {
                $response['logs'] = $opportunity->getLogs();
                $response['received'] = $opportunity->getReceived()->format('d/m/Y H:i:s');
            }
        }

        return $this->json($response);
    }

}
