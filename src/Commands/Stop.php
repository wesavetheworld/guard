<?php namespace Avram\Guard\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Stop extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('stop')
            ->setDescription('Stop watching sites for changes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        if (!guard_running()) {
            $this->error("Guard is NOT running!");
        }

        $pids = guard_get_pids();
        system('kill -9 '.$pids);
        unlink(guard_pidfile());

        $output->writeln("Guard should be stopped now.");
    }

}