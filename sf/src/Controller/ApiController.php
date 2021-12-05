<?php

namespace App\Controller;

use App\Entity\Market;
use App\Service\CcxtService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
