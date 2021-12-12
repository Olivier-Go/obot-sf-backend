<?php

namespace App\Controller;

use App\Entity\Market;
use App\Entity\Opportunity;
use App\Entity\Order;
use App\Service\CcxtService;
use App\Service\OpportunityService;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    private CcxtService $ccxtService;
    private OpportunityService $opportunityService;
    private OrderService $orderService;

    public function __construct(CcxtService $ccxtService, OpportunityService $opportunityService, OrderService $orderService)
    {
        $this->ccxtService = $ccxtService;
        $this->opportunityService = $opportunityService;
        $this->orderService = $orderService;
    }

    /**
     * @Route("/fetch/balance/{id<\d+>}", name="api_fetch_balance", methods="POST")
     */
    public function fetchBalance(Market $market): Response
    {
        $balance = $this->ccxtService->fetchBalance($market);

        if (!$balance) return $this->json(['message' => $market->getName() . ': fetch balance error.'], Response::HTTP_INTERNAL_SERVER_ERROR);

        return $this->json([
            $balance
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/opportunity/new", name="api_opportunity_new", methods="POST")
     */
    public function newOpportunity(Request $request): Response
    {
        $data = $request->getContent();
        $opportunity = $this->opportunityService->createOpportunity($data);

        if (!$opportunity instanceof Opportunity) {
            return $this->json($opportunity, Response::HTTP_BAD_REQUEST);
        }

        // Simulation traitement transaction
        sleep(10);

        return $this->json([
            'message' => 'Opportunity ' . $opportunity->getId() . ' created.',
        ], Response::HTTP_CREATED);
    }

    /**
     * @Route("/order/new", name="api_order_new", methods={"POST"})
     */
    public function newOrder(Request $request): Response
    {
        $data = $request->getContent();
        $order = $this->orderService->createOrder($data);

        if (!$order instanceof Order) {
            return $this->json($order, Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'message' => 'Order ' . $order->getId() . ' created.',
        ], Response::HTTP_CREATED);
    }

}
