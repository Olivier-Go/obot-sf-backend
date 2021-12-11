<?php

namespace App\Controller;

use App\Repository\OpportunityRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OpportunityController extends AbstractController
{
    /**
     * @Route("/opportunity", name="opportunity")
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

}
