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
            ->addOption('cmd', 'c',InputOption::VALUE_REQUIRED, 'start|stop|restart')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cmd = $input->getOption('cmd');

        switch ($cmd) {
            case 'stop':
                $this->stopNode();
                return Command::SUCCESS;

            case 'start':
                $this->startNode();
                return Command::SUCCESS;

            case 'restart':
                if (!empty($this->getNodeProcessPids())) {
                    $this->startNode();
                }
                return Command::SUCCESS;
        }

        throw new InvalidOptionException();
    }

    private function stopNode()
    {
        $this->killNodeExistingProcess();
        echo json_encode(['app' => ['state' => 'stopped'], 'server' => ['state' => 'stopped']]).PHP_EOL;
    }

    private function startNode()
    {
        $this->killNodeExistingProcess();
        $process = new Process(['node', './src/index.js']);
        $process->setWorkingDirectory($this->params->get('node_working_dir'));
        $process->run(function ($type, $buffer) {
            $resp = [];
            $data = json_decode($buffer);
            if (isset($data->app)) $resp['app'] = $data->app;
            if (isset($data->server)) $resp['server'] = $data->server;
            echo json_encode($resp).PHP_EOL;
        });
    }

    private function killNodeExistingProcess(): void
    {
        $pids = $this->getNodeProcessPids();
        foreach ($pids as $pid) {
            $process = new Process(['kill', '-9', $pid]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }
    }

    private function getNodeProcessPids(): array
    {
        $pids = [];
        $process = Process::fromShellCommandline('ps -ef | grep -e "[a]pp.js" -e "[w]s.js"');
        $process->run();
        $stdout = array_filter(explode("\n", $process->getOutput()), 'strlen');
        foreach ($stdout as $line) {
            $pid = array_values(array_filter(explode(" ", $line), 'strlen' ))[1];
            $pids[] = $pid;
        }
        return $pids;
    }
}
