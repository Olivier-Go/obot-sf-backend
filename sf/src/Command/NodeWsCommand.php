<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class NodeWsCommand extends Command
{
    protected static $defaultName = 'app:node-ws';
    protected static $defaultDescription = 'Node Websocket Server';
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('cmd', 'c',InputOption::VALUE_REQUIRED, 'start|stop')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cmd = $input->getOption('cmd');
        $this->killNodeExistingProcess();

        if ($cmd === 'stop') {
            echo json_encode(['app' => ['state' => 'stopped'], 'server' => ['state' => 'stopped']]).PHP_EOL;
            return Command::SUCCESS;
        }
        if ($cmd === 'start') {
            $process = new Process(['node', './src/index.js']);
            $process->setWorkingDirectory($this->params->get('node_working_dir'));

            $process->run(function ($type, $buffer) {
                $resp = [];
                $data = json_decode($buffer);
                if (isset($data->app)) $resp['app'] = $data->app;
                if (isset($data->server)) $resp['server'] = $data->server;
                echo json_encode($resp).PHP_EOL;
            });

            return Command::SUCCESS;
        }

        throw new InvalidOptionException();
    }

    private function killNodeExistingProcess()
    {
        $process = Process::fromShellCommandline('ps -ef | grep -e "[a]pp.js" -e "[w]s.js"');
        $process->run();
        $stdout = array_filter(explode("\n", $process->getOutput()), 'strlen');

        foreach ($stdout as $line) {
            $pid = array_values(array_filter(explode(" ", $line), 'strlen' ))[1];
            $process = new Process(['kill', '-9', $pid]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }
    }
}
