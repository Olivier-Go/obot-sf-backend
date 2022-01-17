<?php

namespace App\Controller;

use App\Entity\Opportunity;
use App\Repository\OpportunityRepository;
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
    /**
     * @Route("/", name="opportunity_index")
     */
    public function index(Request $request, OpportunityRepository $opportunityRepository, PaginatorInterface $paginator): Response
    {
        $page = $request->query->getInt('page', 1);
        $maxItemPerPage = !empty($request->query->getInt('maxItemPerPage')) ? $request->query->getInt('maxItemPerPage') : 20;

        $opportunitiesQuery = $opportunityRepository->findAllQB();
        $paginatedOpportunities = $paginator->paginate(
            $opportunitiesQuery,
            $page,
            $maxItemPerPage
        );

        return $this->render('opportunity/index.html.twig', [
            'opportunities' => $paginatedOpportunities
        ]);
    }


    /**
     * @Route("/log", name="opportunity_log", methods={"POST"})
     * @throws Exception
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
