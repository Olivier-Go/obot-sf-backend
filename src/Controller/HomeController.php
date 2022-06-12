<?php

namespace App\Controller;

use App\Entity\Balance;
use App\Repository\BalanceRepository;
use App\Repository\MarketRepository;
use App\Repository\TickerRepository;
use App\Service\CcxtService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(TickerRepository $tickerRepository): Response
    {
        $tickers = $tickerRepository->findAll();

        return $this->render('home/index.html.twig', [
            'tickers' => $tickers,
        ]);
    }

    /**
     * @Route("/tickers/data", name="tickers_data")
     */
    public function tickersData(TickerRepository $tickerRepository, MarketRepository $marketRepository, BalanceRepository $balanceRepository, CcxtService $ccxtService, ManagerRegistry $doctrine): Response
    {
        $tickers = $tickerRepository->findAll();
        $markets = $marketRepository->findAll();

        foreach ($tickers as $ticker) {
            $currencies = explode('/', $ticker->getName());
            foreach ($currencies as $currency) {
                $balance = $balanceRepository->findOneBy(['market' => $ticker->getMarket(), 'currency' => $currency]);
                if (!$balance instanceof Balance) {
                    $balance = new Balance();
                    $balance->setMarket($ticker->getMarket());
                    $balance->setTicker($ticker);
                    $balance->setCurrency($currency);
                    $doctrine->getManager()->persist($balance);
                }
            }
        }

        foreach ($markets as $market) {
            $market = $ccxtService->fetchTickerInfos($market);
            $market = $ccxtService->fetchAccountAllBalances($market);
            $doctrine->getManager()->persist($market);
        }

        $doctrine->getManager()->flush();

        return $this->render('home/_tickers_data.html.twig', [
            'tickers' => $tickers,
        ]);
    }
}
