<?php

namespace App\Controller;

use App\Form\TradingType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/trading")
 */
class TradingController extends AbstractController
{
    /**
     * @Route("/", name="trading_index")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(TradingType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd($form);
        }

        return $this->renderForm('trading/index.html.twig', [
            'form' => $form,
        ]);
    }
}
