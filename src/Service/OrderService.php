<?php

namespace App\Service;

use App\Entity\Order;
use App\Repository\MarketRepository;
use App\Repository\OrderRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class OrderService
{
    private DenormalizerInterface $denormalizer;
    private ValidatorInterface $validator;
    private MarketRepository $marketRepository;
    private ManagerRegistry $doctrine;
    private OrderRepository $orderRepository;
    private PaginatorInterface $paginator;
    private HubInterface $hub;
    private Environment $twig;

    public function __construct(DenormalizerInterface $denormalizer, ValidatorInterface $validator, MarketRepository $marketRepository, ManagerRegistry $doctrine, OrderRepository $orderRepository, PaginatorInterface $paginator, HubInterface $hub, Environment $twig)
    {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->marketRepository = $marketRepository;
        $this->doctrine = $doctrine;
        $this->orderRepository = $orderRepository;
        $this->paginator = $paginator;
        $this->hub = $hub;
        $this->twig = $twig;
    }

    public function denormalizeOrder(array $orderArr)
    {
        $order = $this->denormalizer->denormalize($orderArr, Order::class);
        $errors = $this->validator->validate($order);
        if (count($errors) > 0) {
            $msgErrors = [];
            foreach ($errors as $error) {
                $msgErrors[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }
            return $msgErrors;
        }

        $order->setMarket($this->marketRepository->find($orderArr['market']));

        return $order;
    }

    public function createOrder(Order $order): Order
    {
        $em = $this->doctrine->getManager();
        $em->persist($order);
        $em->flush();

        $side = strtoupper($order->getSide());
        $market = strtoupper($order->getMarket());

        $this->hub->publish(new Update(
            'notification',
            $this->twig->render('broadcast/Notification.stream.html.twig', [
                'type' => 'info',
                'message' => "Nouvel ordre $side sur $market"
            ])
        ));

        return $order;
    }

    public function updateOrder(Order $order): ?Order
    {
        $em = $this->doctrine->getManager();
        $order = $em->getRepository(Order::class)->findOneBy(['orderId' => $order['orderId']]);

        if (!$order instanceof Order) return null;

        $em = $this->doctrine->getManager();
        $em->persist($order);
        $em->flush();

        return $order;
    }

    public function paginateOrders(int $page, int $maxItemPerPage): PaginationInterface
    {
        $query = $this->orderRepository->findAllQB();
        $limit = $maxItemPerPage;
        if ($maxItemPerPage === 0) {
            $limit = count($query->getResult()) > 0 ? count($query->getResult()) : 20;
        }

        return $this->paginator->paginate(
            $query,
            $page,
            $limit
        );
    }

}