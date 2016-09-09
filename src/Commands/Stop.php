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

        $pidFile = GUARD_USER_FOLDER.DIRECTORY_SEPARATOR.'.pidfile';

        if (!is_file($pidFile)) {
            $output->writeln("Guard is NOT running!");
            exit(0);
        }

        $pid = trim(file_get_contents($pidFile));
        system('kill -9 '.$pid);
        unlink($pidFile);

        $output->writeln("Guard should be stopped now.");
    }

}