<?php

namespace App\Controller;

use App\Entity\Opportunity;
use App\Repository\MarketRepository;
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
    private WorkerService $workerService;
    private MarketRepository $marketRepository;

    public function __construct(CcxtService $ccxtService, OpportunityService $opportunityService, OrderService $orderService, WorkerService $workerService, MarketRepository $marketRepository)
    {
        $this->ccxtService = $ccxtService;
        $this->opportunityService = $opportunityService;
        $this->orderService = $orderService;
        $this->workerService = $workerService;
        $this->marketRepository = $marketRepository;
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

        //$opportunity = $this->workerService->execute($opportunity);

        $this->opportunityService->createOpportunity($opportunity);

        return $this->json([
            'message' => 'Opportunity ' . $opportunity->getId() . ' created.',
        ], Response::HTTP_CREATED);
    }

    /** ================> TEST ================> */
    /**
     * @Route("/ccxt/order/send", methods={"POST"})
     */
    public function ccxtSendOrder(Request $request)
    {
        $data = json_decode($request->getContent());
        $ticker = $data->ticker;
        $amount = $data->amount;
        $sellMarket = $this->marketRepository->find($data->sellMarket);
        $buyMarket = $this->marketRepository->find($data->buyMarket);

        if ($sellMarket) {
            dd($this->ccxtService->sendSellMarketOrder($sellMarket, $ticker, $amount));
        }
        if ($buyMarket) {
            dd($this->ccxtService->sendBuyMarketOrder($buyMarket, $ticker, $amount));
        }
    }

    /**
     * @Route("/ccxt/orders", methods={"POST"})
     */
    public function ccxtOrders(Request $request): Response
    {
        $data = json_decode($request->getContent());
        $market = $this->marketRepository->find($data->market);

        dd($this->ccxtService->fetchOrders($market, true));
    }

    /**
     * @Route("/ccxt/order/fetch", methods={"POST"})
     */
    public function ccxtFetchOrder(Request $request): Response
    {
        $data = json_decode($request->getContent());
        $market = $this->marketRepository->find($data->market);
        $ticker = $data->ticker;
        $orderId = $data->orderId;

        dd($this->ccxtService->fetchOrder($market, $ticker, $orderId));
    }

    /**
     * @Route("/ccxt/order/cancel", methods={"POST"})
     */
    public function ccxtCancelOrder(Request $request): Response
    {
        $data = json_decode($request->getContent());
        $market = $this->marketRepository->find($data->market);
        $ticker = $data->ticker;
        $orderId = $data->orderId;

        dd($this->ccxtService->cancelOrder($market, $ticker, $orderId));
    }

}
