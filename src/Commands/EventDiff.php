<?php

namespace Avram\Guard\Commands;


use Avram\Guard\FileEvent;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EventDiff extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('event:diff')
            ->setDescription('Show differences for event')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('id', InputArgument::REQUIRED, 'ID of the event to allow'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $id     = $input->getArgument('id');
        $events = $this->eventsFile->getEvents();

        if (!isset($events[$id - 1])) {
            $this->error("Event with ID #{$id} does not exist!");
        }

        /** @var FileEvent $event */
        $event = $events[$id - 1];
        $this->diff($event);

    }

    public function diff(FileEvent $event)
    {
        $type               = $event->getType();
        $site               = $event->getSite($this->guardFile);
        $filePath           = $event->getPath();
        $relativeFilePath   = ltrim(str_replace($site->getPath(), '', $filePath), DIRECTORY_SEPARATOR);
        $quarantineFilePath = $site->quarantinePath($relativeFilePath);

        $differ = new Differ();

        switch ($type) {
            case 'MOVED_TO':
            case 'CREATE':
                if (is_file($quarantineFilePath)) {
                    $this->outputInterface->writeln($differ->diff('', file_get_contents($quarantineFilePath)));
                } else {
                    $this->outputInterface->writeln("Nothing to diff: {$quarantineFilePath} NOT found!");
                }
                break;

            case 'MODIFY':
                if (is_file($quarantineFilePath)) {
                    $this->outputInterface->writeln($differ->diff(file_get_contents($filePath), file_get_contents($quarantineFilePath)));
                } else {
                    $this->outputInterface->writeln("Nothing to diff: {$quarantineFilePath} NOT found!");
                }

                break;

            case 'MOVED_FROM':
            case 'DELETE':
                if (is_file($filePath)) {
                    $this->outputInterface->writeln($differ->diff(file_get_contents($filePath), ''));
                } else {
                    $this->outputInterface->writeln("Nothing to diff: {$filePath} NOT found!");
                }
                break;
        }

    }
}
