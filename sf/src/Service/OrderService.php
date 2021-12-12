<?php

namespace App\Service;

use App\Entity\Order;
use App\Repository\MarketRepository;
use App\Repository\TickerRepository;
use DateInterval;
use DateTime;
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

    public function __construct(DenormalizerInterface $denormalizer, ValidatorInterface $validator, TickerRepository $tickerRepository, MarketRepository $marketRepository, ManagerRegistry $doctrine)
    {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->tickerRepository = $tickerRepository;
        $this->marketRepository = $marketRepository;
        $this->doctrine = $doctrine;
    }

    public function createOrder(String $data)
    {
        $data = json_decode($data);
        $openTimestamp = number_format($data->timestamp  / 1000, 0, ',', '');
        if (!empty($openTimestamp)) {
            $date = DateTime::createFromFormat('U', $openTimestamp);
            $date->add(new DateInterval('PT1H'));
            $data->opened = $date->format('m/d/Y H:i:s');
        }
        $lastTradeTimestamp = number_format($data->lastTradeTimestamp / 1000, 0, ',', '');
        if (!empty($lastTradeTimestamp)) {
            $date = DateTime::createFromFormat('U', $lastTradeTimestamp);
            $date->add(new DateInterval('PT1H'));
            $data->lastTrade = $date->format('m/d/Y H:i:s');
        }

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
}