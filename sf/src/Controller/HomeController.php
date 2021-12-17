<?php

namespace App\Controller;

use App\Entity\Balance;
use App\Repository\BalanceRepository;
use App\Repository\TickerRepository;
use App\Service\CcxtService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home", methods={"GET"})
     */
    public function index(TickerRepository $tickerRepository): Response
    {
        $tickers = $tickerRepository->findAll();

        return $this->render('home/index.html.twig', [
            'tickers' => $tickers,
        ]);
    }

    /**
     * @Route("/tickers/data", name="tickers_data", methods={"GET"})
     */
    public function tickersData(TickerRepository $tickerRepository, BalanceRepository $balanceRepository, CcxtService $ccxtService, ManagerRegistry $doctrine): Response
    {
        $tickers = $tickerRepository->findAll();

        foreach ($tickers as $ticker) {
            $ticker = $ccxtService->fetchTickerInfos($ticker);
            $currencies = explode('/', $ticker->getName());
            foreach ($currencies as $currency) {
                $balance = $balanceRepository->findOneBy(['market' => $ticker->getMarket(), 'currency' => $currency]);
                if (!$balance instanceof Balance) {
                    $balance = new Balance();
                    $balance->setMarket($ticker->getMarket());
                    $balance->setTicker($ticker);
                    $balance->setCurrency($currency);
                }
                $balance = $ccxtService->fetchAccountBalance($balance);
                $doctrine->getManager()->persist($balance);
            }

            $doctrine->getManager()->persist($ticker);
        }

        $doctrine->getManager()->flush();

        return $this->render('home/_tickers_data.html.twig', [
            'tickers' => $tickers,
        ]);
    }
}
