<?php

namespace App\Service;

use App\Entity\Opportunity;
use App\Repository\MarketRepository;
use App\Repository\TickerRepository;
use App\Utils\Tools;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OpportunityService extends Tools
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
        $data->received = $this->convertTimestampSec($data->received);

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