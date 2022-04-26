<?php

namespace App\Service;

use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class NodeService
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @throws Exception
     */
    public function command(string $cmd): string
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'app:node-ws',
            '--cmd' => $cmd,
        ]);
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $output->fetch();
    }
}