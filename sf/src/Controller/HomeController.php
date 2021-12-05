<?php

namespace App\Controller;

use App\Repository\MarketRepository;
use App\Service\CcxtService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(MarketRepository $marketRepository, CcxtService $ccxtService): Response
    {
        $markets = $marketRepository->findAll();
        $tickers = new ArrayCollection();

        foreach ($markets as $market) {
            foreach ($market->getTickers() as $ticker) {
                $ticker = $ccxtService->fetchMarketTickerData($market, $ticker);
                $tickers->add($ticker);
            }
        }

        return $this->render('home/index.html.twig', [
            'tickers' => $tickers
        ]);
    }

}
