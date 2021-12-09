<?php

namespace App\Controller;

use App\Entity\Market;
use App\Entity\Opportunity;
use App\Repository\MarketRepository;
use App\Repository\TickerRepository;
use App\Service\CcxtService;
use DateInterval;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api", name="api_")
 */
class ApiController extends AbstractController
{
    private CcxtService $ccxtService;

    public function __construct(CcxtService $ccxtService)
    {
        $this->ccxtService = $ccxtService;
    }

    /**
     * @Route("/fetch/balance/{id<\d+>}", name="fetch_balance", methods="POST")
     */
    public function fetchBalance(Request $request, Market $market): Response
    {
        $balance = $this->ccxtService->fetchBalance($market);

        if (!$balance) return $this->json(['message' => $market->getName() . ': fetch balance error.'], Response::HTTP_INTERNAL_SERVER_ERROR);

        return $this->json([
            $balance
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/arbitrage/opportunity/add", name="arbitrage_opportunity_add", methods="POST")
     */
    public function addOpportunity(Request $request, DenormalizerInterface $denormalizer, ValidatorInterface $validator, TickerRepository $tickerRepository, MarketRepository $marketRepository, ManagerRegistry $doctrine): Response
    {
        $data = json_decode($request->getContent());
        if (isset($data->received)) {
            $timestamp = $data->received;
            if (!empty($timestamp)) {
                $date = DateTime::createFromFormat('U', $timestamp);
                $date->add(new DateInterval('PT1H'));
                $data->received = $date->format('d/m/Y H:i:s');
            }
        }

        $opportunity = $denormalizer->denormalize($data, Opportunity::class);
        $errors = $validator->validate($opportunity);
        if (count($errors) > 0) {
            $msgErrors = [];
            foreach ($errors as $error) {
                $msgErrors[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }
            return $this->json($msgErrors, Response::HTTP_BAD_REQUEST);
        }

        $opportunity->setTicker($tickerRepository->find($data->ticker));
        $opportunity->setBuyMarket($marketRepository->find($data->buyMarket));
        $opportunity->setSellMarket($marketRepository->find($data->sellMarket));

        $em = $doctrine->getManager();
        $em->persist($opportunity);
        $em->flush();

        return $this->json([
            'Opportunity created.',
        ], Response::HTTP_CREATED);
    }
}
