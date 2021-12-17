<?php

namespace App\Controller;

use App\Entity\Market;
use App\Entity\Opportunity;
use App\Entity\Order;
use App\Service\CcxtService;
use App\Service\OpportunityService;
use App\Service\OrderService;
use App\Service\WorkerService;
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
    private WorkerService $workerService;

    public function __construct(CcxtService $ccxtService, OpportunityService $opportunityService, OrderService $orderService, WorkerService $workerService)
    {
        $this->ccxtService = $ccxtService;
        $this->opportunityService = $opportunityService;
        $this->orderService = $orderService;
        $this->workerService = $workerService;
    }

    /**
     * @Route("/opportunity/new", name="api_opportunity_new", methods="POST")
     */
    public function newOpportunity(Request $request): Response
    {
        $data = $request->getContent();
        $opportunity = $this->opportunityService->denormalizeOpportunity($data);

        if (!$opportunity instanceof Opportunity) {
            return $this->json($opportunity, Response::HTTP_BAD_REQUEST);
        }

        dd($this->workerService->execute($opportunity));

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
            'message' => 'Order ' . $order->getClientOrderId() . ' created.',
        ], Response::HTTP_CREATED);
    }

    /**
     * @Route("/order/update/all/{id<\d+>}", name="api_order_update_all", methods={"POST"})
     */
    public function updateAllOrders(Request $request, Market $market): Response
    {
        $exchangeOrders = $this->ccxtService->fetchOrders($market);
        $orders = $this->orderService->updateOrders($exchangeOrders);

        return $this->json([
            'message' => count($orders) . ' order(s) updated.',
        ], Response::HTTP_CREATED);
    }

}
