<?php

namespace App\Service;

use App\Entity\Order;
use App\Repository\MarketRepository;
use App\Repository\OrderRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderService
{
    private DenormalizerInterface $denormalizer;
    private ValidatorInterface $validator;
    private MarketRepository $marketRepository;
    private ManagerRegistry $doctrine;

    public function __construct(DenormalizerInterface $denormalizer, ValidatorInterface $validator, MarketRepository $marketRepository, ManagerRegistry $doctrine)
    {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->marketRepository = $marketRepository;
        $this->doctrine = $doctrine;
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

        return $order;
    }

    public function updateOrder(array $order): ?Order
    {
        $em = $this->doctrine->getManager();
        $order = $em->getRepository(Order::class)->findOneBy(['orderId' => $order['orderId']]);

        if (!$order instanceof Order) return null;

        $em = $this->doctrine->getManager();
        $em->persist($order);
        $em->flush();

        return $order;
    }

}