<?php

namespace App\Controller;

use App\Service\NodeService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/node")
 */

class NodeController extends AbstractController
{
    /**
     * @Route("/", name="node", methods={"POST"})
     * @throws Exception
     */
    public function index(Request $request, NodeService $nodeService): Response
    {
        $data = $request->toArray();
        $content = $nodeService->command($data['cmd']);
        return new Response($content);
    }

    /**
     * @Route("/console", name="node_console")
     */
    public function console(): Response
    {
        return $this->render('console/index.html.twig', []);
    }
}
