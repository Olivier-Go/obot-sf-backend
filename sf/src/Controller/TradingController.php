<?php

namespace App\Controller;

use App\Entity\Market;
use App\Entity\Ticker;
use App\Form\TradingType;
use App\Repository\MarketRepository;
use App\Repository\TickerRepository;
use App\Service\SessionService;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/trading")
 */
class TradingController extends AbstractController
{
    /**
     * @Route("/", name="trading_index")
     * @throws Exception
     */
    public function index(Request $request, SessionService $sessionService): Response
    {
        $sessionKey = 'trading';
        $session = $request->getSession();
        $params = $sessionService->sessionToForm($session, $sessionKey);

        $form = $this->createForm(TradingType::class, $params);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sessionService->formToSession($form, $session, $sessionKey);
//            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
//                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
//                return $this->render('trading/_orderbook.stream.html.twig', [
//                    'stream_target' => 'sellMarket-orderbook'
//                ]);
//            }
            return $this->redirectToRoute('trading_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('trading/index.html.twig', [
            'form' => $form,
        ]);
    }


    /**
     * @Route("/orderbook", name="trading_orderbook", methods={"POST"})
     */
    public function orderbook(Request $request): Response
    {
        $data = $request->toArray();
        dd($data);
        $form = $this->createForm(TradingType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd($form);
        }

        return $this->renderForm('trading/index.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/ws/data", name="trading_ws_data", methods={"POST"})
     */
    public function wsMarketData(Request $request, HttpClientInterface $client, MarketRepository $marketRepository, TickerRepository $tickerRepository): Response
    {
        $data = $request->toArray();
        $rtn = [];

        if ($data['market'] && $data['ticker']) {
            $market = $marketRepository->find($data['market']);
            $ticker = $tickerRepository->find($data['ticker']);

            if ($market instanceof Market && $ticker instanceof Ticker && $market->getApiUrl()) {
                switch ($market->getName()) {
                    case 'kucoin':
                        try {
                            $response = $client->request('POST', $market->getApiUrl() . '/api/v1/bullet-public');
                            if (200 === $response->getStatusCode()) {
                                $rsp = $response->toArray();
                                $date = new DateTime();
                                $rtn = [
                                    'exchange' => $market->getName(),
                                    'endpoint' => $rsp['data']['instanceServers'][0]['endpoint'] . '?token=' . $rsp['data']['token'] . '&[connectId=' . $date->getTimestamp() . ']',
                                    'symbol' => str_replace('/', '-', $ticker->getName())
                                ];
                            }
                        } catch (TransportExceptionInterface $e) {
                        }
                        break;
                }
            }
        }

        return $this->json($rtn);
    }
}
