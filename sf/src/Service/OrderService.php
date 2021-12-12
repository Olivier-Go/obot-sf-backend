<?php

namespace App\Service;

use App\Entity\Order;
use App\Repository\MarketRepository;
use App\Repository\OrderRepository;
use App\Repository\TickerRepository;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderService
{
    private DenormalizerInterface $denormalizer;
    private ValidatorInterface $validator;
    private TickerRepository $tickerRepository;
    private MarketRepository $marketRepository;
    private ManagerRegistry $doctrine;
    private OrderRepository $orderRepository;

    public function __construct(DenormalizerInterface $denormalizer, ValidatorInterface $validator, TickerRepository $tickerRepository, MarketRepository $marketRepository, OrderRepository $orderRepository, ManagerRegistry $doctrine)
    {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->tickerRepository = $tickerRepository;
        $this->marketRepository = $marketRepository;
        $this->orderRepository = $orderRepository;
        $this->doctrine = $doctrine;
    }

    public function createOrder(String $data)
    {
        $data = json_decode($data);
        $order = $this->denormalizer->denormalize($data, Order::class);

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

        $order->setTicker($this->tickerRepository->find($data->ticker));
        $order->setMarket($this->marketRepository->find($data->market));

        $em = $this->doctrine->getManager();
        $em->persist($order);
        $em->flush();

        return $order;
    }

    public function updateOrders(Array $exchangeOrders): array
    {
        $orders = [];

        foreach ($exchangeOrders as $exchangeOrder) {
            $exchangeOrder = $this->denormalizer->denormalize($exchangeOrder, Order::class);
            $order = $this->orderRepository->findOneBy(['clientOrderId' => $exchangeOrder->getClientOrderId()]);
            if ($order instanceof Order) {
                $em = $this->doctrine->getManager();
                $exchangeOrder->setId($order->getId());
                $exchangeOrder->setTicker($order->getTicker());
                $exchangeOrder->setMarket($order->getMarket());
                $exchangeOrder->setCreated($order->getCreated());

                $em->remove($order);
                $em->flush();

                $em->persist($exchangeOrder);
                $metadata = $em->getClassMetaData(get_class($exchangeOrder));
                $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
                $metadata->setIdGenerator(new AssignedGenerator());
                $em->flush();

                $orders[] = $exchangeOrder;
            }
        }

        return $orders;
    }

}