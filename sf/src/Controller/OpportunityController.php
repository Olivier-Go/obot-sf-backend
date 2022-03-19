<?php

namespace App\Controller;

use App\Entity\Opportunity;
use App\Repository\OpportunityRepository;
use App\Service\ExportService;
use Knp\Component\Pager\PaginatorInterface;
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

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * @Route("/", name="opportunity_index")
     */
    public function index(Request $request, OpportunityRepository $opportunityRepository, PaginatorInterface $paginator): Response
    {
        $page = $request->query->getInt('page', 1);
        $maxItemPerPage = !empty($request->query->getInt('maxItemPerPage')) ? $request->query->getInt('maxItemPerPage') : 20;

        $query = $opportunityRepository->findAllQB();

        // Export
        $export = $request->get('export');
        if ($export === 'pdf') {
            $params['name'] = 'opportunites';
            $params['template'] = 'opportunity/index.html.twig';
            $params['pagination'] = $paginator->paginate(
                $query,
                1,
                count($query->getResult())
            );
            return $this->exportService->exportpdf($params);
        }

        $pagination = $paginator->paginate(
            $query,
            $page,
            $maxItemPerPage
        );

        return $this->render('opportunity/index.html.twig', [
            'pagination' => $pagination
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
