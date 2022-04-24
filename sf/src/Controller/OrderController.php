<?php

namespace App\Controller;

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
     * @Route("/{id}/edit", name="order_edit", methods={"GET", "POST"})
     */
    /*public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }*/

}
