<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", name="order_index")
     * @Route("/{id}", name="order_show")
     */
    public function index(Request $request, OrderRepository $orderRepository, PaginatorInterface $paginator, ?Order $order): Response
    {
        $page = $request->query->getInt('page', 1);
        $maxItemPerPage = !empty($request->query->getInt('maxItemPerPage')) ? $request->query->getInt('maxItemPerPage') : 20;

        $ordersQuery = $orderRepository->findQB($order);
        $paginatedOrders = $paginator->paginate(
            $ordersQuery,
            $page,
            $maxItemPerPage
        );

        return $this->render('order/index.html.twig', [
            'orders' => $paginatedOrders,
            'order' => $order
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
