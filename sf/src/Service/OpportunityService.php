<?php

namespace App\Service;

use App\Entity\Opportunity;
use App\Repository\MarketRepository;
use App\Repository\TickerRepository;
use DateInterval;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OpportunityService
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

    public function createOpportunity(String $data)
    {
        $data = json_decode($data);
        if (isset($data->received)) {
            $timestamp = $data->received;
            if (!empty($timestamp)) {
                $date = DateTime::createFromFormat('U', $timestamp);
                $date->add(new DateInterval('PT1H'));
                $data->received = $date->format('m/d/Y H:i:s');
            }
        }

        $opportunity = $this->denormalizer->denormalize($data, Opportunity::class);
        $errors = $this->validator->validate($opportunity);
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

        $opportunity->setTicker($this->tickerRepository->find($data->ticker));
        $opportunity->setBuyMarket($this->marketRepository->find($data->buyMarket));
        $opportunity->setSellMarket($this->marketRepository->find($data->sellMarket));

        $em = $this->doctrine->getManager();
        $em->persist($opportunity);
        $em->flush();

        return $opportunity;
    }

}