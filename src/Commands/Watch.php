<?php namespace Avram\Guard\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Watch extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('watch')
            ->setDescription('Watch all sites for changes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);


        $output->writeln("Setting up watches on PATH. This may take a while...");
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
//                echo 'OUT > '.$buffer;
                list($path, $event) = explode(' ', $buffer);
                $this->handle($path, $event);
            }
        });

        echo $process->getOutput();
    }

    protected function handle($filePath, $event)
    {

    }
}