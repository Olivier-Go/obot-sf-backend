<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\ExportService;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    private ExportService $exportService;
    private OrderService $orderService;

    public function __construct(ExportService $exportService, OrderService $orderService)
    {
        $this->exportService = $exportService;
        $this->orderService = $orderService;
    }

    /**
     * @Route("/", name="order_index")
     */
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $maxItemPerPage = !empty($request->query->getInt('maxItemPerPage')) ? $request->query->getInt('maxItemPerPage') : 20;

        // Export
        $export = $request->get('export');
        if ($export === 'pdf') {
            $params['name'] = 'ordres';
            $params['template'] = 'order/index.html.twig';
            $params['pagination'] = $this->orderService->paginateOrders(
                1,
                0
            );
            return $this->exportService->exportpdf($params);
        }

        return $this->render('order/index.html.twig', [
            'pagination' =>  $this->orderService->paginateOrders($page, $maxItemPerPage)
        ]);
    }

    /**
     * @Route("/{id}", name="order_show", methods={"POST"})
     */
    public function show(Order $order): Response
    {
        $response = [];
        $response['opened'] = $order->getOpened()->format('d/m/Y H:i:s');
        $response['html'] = $this->renderView('order/show.html.twig', ['order' => $order]);
        return $this->json($response);
    }
}
