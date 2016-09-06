<?php

namespace Avram\Guard\Command;

use Avram\Guard\Exceptions\GuardFileException;
use Avram\Guard\Service\GuardFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Start extends Command
{
    protected function configure()
    {
        $this
            ->setName('start')
            ->setDescription('Say hello')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $guardFile = null;

        try {
            $guardFile = new GuardFile();
        } catch (GuardFileException $ex) {
            $output->writeln($ex->getMessage());
            exit(1);
        }

        $path = $guardFile->getPath();

        $output->writeln("Setting up watches on {$path}. This may take a while...");
        $output->writeln("Press CTRL+C to abort.");

        $process = new Process("inotifywait -m -r -e modify -e create -e move -e delete --format '%w%f %e' {$path}");
        $process->setTimeout(0);
        $process->start();

        $pid = $process->getPid();
        $output->writeln("Watcher PID is {$pid}");

//        $pidPath = str_replace('/', '-', $path);
//        file_put_contents("~/.guard/pids/{$pidPath}.pid", $pid);

        $process->wait(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                if (stripos($buffer, "Setting up watches") !== 0) {
                    $output->writeln($buffer);
                }
            } else {
                echo 'OUT > '.$buffer;
            }
        });

        echo $process->getOutput();
    }
}