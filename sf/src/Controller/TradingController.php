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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/trading")
 */
class TradingController extends AbstractController
{
    private SessionService $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * @Route("/", name="trading_index")
     * @throws Exception
     */
    public function index(Request $request, SessionService $sessionService): Response
    {
        $sessionKey = 'trading';
        $session = $request->getSession();
        $form = $this->createFormFromSession($session, $sessionKey);

        if ($request->isMethod('POST')) {
            $form->submit($request->toArray());
            $sessionService->formToSession($form, $session, $sessionKey);
            $response = $this->renderForm("trading/_form.html.twig", [
                'form' => $this->createFormFromSession($session, $sessionKey),
            ]);
            return $this->json($response, Response::HTTP_OK);
        }

        return $this->renderForm("trading/index.html.twig", [
            'form' => $form,
        ]);
    }

    /**
     * @throws Exception
     */
    private function createFormFromSession(SessionInterface $session, string $sessionKey): FormInterface
    {
        $params = $this->sessionService->sessionToForm($session, $sessionKey);
        return $this->createForm(TradingType::class, $params);
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
