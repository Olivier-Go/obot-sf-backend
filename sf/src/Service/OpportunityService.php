<?php

namespace App\Service;

use App\Entity\Opportunity;
use App\Repository\MarketRepository;
use App\Utils\Tools;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OpportunityService extends Tools
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

    public function denormalizeOpportunity(string $data)
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

        $opportunity->setBuyMarket($this->marketRepository->findOneBy(['name' => strtolower($data->buyMarket)]));
        $opportunity->setSellMarket($this->marketRepository->findOneBy(['name' => strtolower($data->sellMarket)]));

        return $opportunity;
    }

    public function createOpportunity(Opportunity $opportunity): Opportunity
    {
        $em = $this->doctrine->getManager();
        $em->persist($opportunity);
        $em->flush();

        return $opportunity;
    }

}