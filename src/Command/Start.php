<?php

namespace Avram\Guard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

use Avram\Guard\Service\GuardFile;
use Avram\Guard\Exceptions\GuardFileException;

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

        $output->writeln('Hello World');

        $process = new Process("inotifywait -m -r -e modify -e create -e move -e delete --format '%w %f %e' {$path}");

        $output->writeln("Watching dir {$path}. Press CTRL+C to abort.");

        $process->setTimeout(0);
        $process->start();

        $process->wait(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $output->writeln($buffer);
            } else {
                echo 'OUT > '.$buffer;
            }
        });

        echo $process->getOutput();
    }
}