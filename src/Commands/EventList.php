<?php namespace Avram\Guard\Commands;

use Avram\Guard\FileEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EventList extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('event:list')
            ->setDescription('List blocked events');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $events = $this->eventsFile->getEvents();

        if (empty($events)) {
            $output->writeln("No blocked events found.");
            exit(0);
        }

        $header    = ['ID', 'Site', 'Path', 'Type', 'Attempts', 'First Attempt', 'Last attempt'];
        $tableRows = [];
        $id        = 0;

        /** @var FileEvent $event */
        foreach ($events as $event) {
            $site = $event->getSite($this->guardFile);

            $row   = [];
            $row[] = ++$id;
            $row[] = $site->getName();
            $row[] = $event->getPath();
            $row[] = $event->getType();
            $row[] = $event->getAttempts();
            $row[] = date('Y-m-d H:i:s', $event->getFirstAttempt());
            $row[] = date('Y-m-d H:i:s', $event->getLastAttempt());

            $tableRows[] = $row;
        }

        $this->table($header, $tableRows);
    }
}