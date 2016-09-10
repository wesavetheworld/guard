<?php

namespace Avram\Guard\Commands;


use Avram\Guard\FileEvent;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EventRemove extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('event:remove')
            ->setDescription('Forget blocked event(s)')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('nr', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Number of the event to allow'),
                    new InputOption('silent', 's', InputOption::VALUE_NONE, 'Do not ask any questions'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $ids    = $input->getArgument('nr');
        $silent = $input->getOption('silent');
        $events = $this->eventsFile->getEvents();

        if ($ids[0] === 'all') {
            $ids = range(1, count($events));
        }

        foreach ($ids as $id) {
            if (!isset($events[$id - 1])) {
                $this->error("Event #{$id} does not exist!");
            }

            /** @var FileEvent $event */
            $event = $events[$id - 1];
            if (!$silent) {
                $type     = $event->getType();
                $filePath = $event->getPath();
                $this->outputInterface->writeln("You are about to forget event {$type} on {$filePath}.");
                $this->outputInterface->writeln("Note that this can NOT be undone!");
                if (!$this->confirm('Do you wish to continue?')) {
                    $this->outputInterface->writeln('');
                    continue;
                }
            }
            $this->forget($event);

        }
    }

    public function forget(FileEvent $event)
    {
        $type               = $event->getType();
        $site               = $event->getSite($this->guardFile);
        $filePath           = $event->getPath();
        $relativeFilePath   = ltrim(str_replace($site->getPath(), '', $filePath), DIRECTORY_SEPARATOR);
        $quarantineFilePath = $site->quarantinePath($relativeFilePath);

        switch ($type) {
            case 'MOVED_TO':
            case 'CREATE':
            case 'MODIFY':
                if (is_file($quarantineFilePath)) {
                    $this->fileSystem->remove($quarantineFilePath);
                    $this->outputInterface->writeln("Forgot {$type} on {$filePath}.");
                } else {
                    $this->error("Quarantined file {$quarantineFilePath} NOT found!");
                }

                break;
            case 'MOVED_FROM':
            case 'DELETE':
                $this->outputInterface->writeln("Forgot {$type} on {$filePath}.");

                break;
        }

        $this->eventsFile->removeEvent($event);
        $this->eventsFile->dump();

    }
}
