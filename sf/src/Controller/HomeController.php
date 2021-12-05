<?php

namespace App\Controller;

use App\Repository\TickerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use ccxt\kucoin as Kucoin;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(TickerRepository $tickerRepository): Response
    {
        $tickers = $tickerRepository->findAll();

        $kucoin = new Kucoin();
        //$markets = $kucoin->load_markets();
        $fluxUsdT = $kucoin->fetch_ticker('FLUX/USDT');

        foreach ($tickers as $ticker) {
            if ($ticker->getName() == $fluxUsdT['symbol']) {
                foreach ($fluxUsdT as $key => $value) {
                    $ticker->$key = $value;
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'tickers' => $tickers
        ]);
    }

}
