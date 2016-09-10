<?php namespace Avram\Guard\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Status extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Check the status of the Guard process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $output->writeln(guard_running() ? "Guard is running." : "Guard is NOT running.");
    }

}