<?php

namespace App\Controller;

use App\Entity\Opportunity;
use App\Entity\Order;
use App\Entity\Parameter;
use App\Repository\MarketRepository;
use App\Repository\ParameterRepository;
use App\Service\CcxtService;
use App\Service\OpportunityService;
use App\Service\OrderService;
use App\Service\WorkerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
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
    private MarketRepository $marketRepository;
    private ContainerBagInterface $params;
    private ParameterRepository $parameterRepository;

    public function __construct(CcxtService $ccxtService, OpportunityService $opportunityService, OrderService $orderService, WorkerService $workerService, MarketRepository $marketRepository, ContainerBagInterface $params, ParameterRepository $parameterRepository)
    {
        $this->ccxtService = $ccxtService;
        $this->opportunityService = $opportunityService;
        $this->orderService = $orderService;
        $this->workerService = $workerService;
        $this->marketRepository = $marketRepository;
        $this->params = $params;
        $this->parameterRepository = $parameterRepository;
    }

    /**
     * @Route("/parameters", name="api_parameters", methods="GET")
     */
    public function parameters(): Response
    {
        $parameter = $this->parameterRepository->findFirst();
        if (!$parameter instanceof Parameter) {
            return $this->json([
                'message' => 'Undefined parameters.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'worker_order_diff' => $parameter->getWorkerOrderDiff(),
            'worker_order_size' => $parameter->getWorkerOrderSize()
        ], Response::HTTP_OK);
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

        $opportunity = $this->workerService->execute($opportunity);

        return $this->json([
            'message' => 'Opportunity ' . $opportunity->getId() . ' created.',
        ], Response::HTTP_CREATED);
    }

    /** ================> TEST ================> */
    /**
     * @Route("/order/new", methods="POST")
     */
    public function newOrder(Request $request): Response
    {
        if ($this->params->get('app_env') === 'prod') {
            return $this->json([
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $order = $this->orderService->denormalizeOrder($data);

        if (!$order instanceof Order) {
            return $this->json($order, Response::HTTP_BAD_REQUEST);
        }

        $this->orderService->createOrder($order);

        return $this->json([
            'message' => 'Order ' . $order->getId() . ' created.',
        ], Response::HTTP_CREATED);
    }

    /**
     * @Route("/ccxt/order/send", methods={"POST"})
     */
    public function ccxtSendOrder(Request $request)
    {
        if ($this->params->get('app_env') === 'prod') {
            return $this->json([
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

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
        if ($this->params->get('app_env') === 'prod') {
            return $this->json([
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent());
        $market = $this->marketRepository->find($data->market);

        dd($this->ccxtService->fetchOrders($market, true));
    }

    /**
     * @Route("/ccxt/orderbook/fetch", methods={"POST"})
     */
    public function ccxtFetchOrderbook(Request $request): Response
    {
        if ($this->params->get('app_env') === 'prod') {
            return $this->json([
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent());
        $market = $this->marketRepository->find($data->market);
        $ticker = $data->ticker;

        dd($this->ccxtService->fetchOrderBook($market, $ticker));
    }

    /**
     * @Route("/ccxt/order/fetch", methods={"POST"})
     */
    public function ccxtFetchOrder(Request $request): Response
    {
        if ($this->params->get('app_env') === 'prod') {
            return $this->json([
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

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
        if ($this->params->get('app_env') === 'prod') {
            return $this->json([
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent());
        $market = $this->marketRepository->find($data->market);
        $ticker = $data->ticker;
        $orderId = $data->orderId;

        dd($this->ccxtService->cancelOrder($market, $ticker, $orderId));
    }

}
