<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NodeController extends AbstractController
{
    /**
     * @Route("/node", name="node")
     */
    public function index(): Response
    {
        return $this->render('node/index.html.twig', []);
    }
}
