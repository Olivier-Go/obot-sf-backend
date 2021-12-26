<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
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
    public function index(Request $request, KernelInterface $kernel): Response
    {
        $data = $request->toArray();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'app:node-ws',
            '--cmd' => $data['cmd'],
        ]);
        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = $output->fetch();
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
