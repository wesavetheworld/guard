<?php

namespace Avram\Guard\Commands;


use Avram\Guard\FileEvent;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EventAllow extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('event:allow')
            ->setDescription('Allow blocked event(s)')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('id', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'ID of the event to allow'),
                    new InputOption('silent', 'S', InputOption::VALUE_OPTIONAL, 'Do not ask any questions', false),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $ids    = $input->getArgument('id');
        $silent = $input->getOption('silent');
        $events = $this->eventsFile->getEvents();

        if ($ids[0] === 'all') {
            $ids = range(1, count($events));
        }

        foreach ($ids as $id) {
            if (!isset($events[$id - 1])) {
                $this->error("Event with ID #{$id} does not exist!");
            }

            /** @var FileEvent $event */
            $event = $events[$id - 1];
            if (!$silent) {
                $type     = $event->getType();
                $filePath = $event->getPath();
                $this->outputInterface->writeln("You are about to allow event {$type} on {$filePath}.");
                $this->outputInterface->writeln("Note that this can NOT be undone!");
                if (!$this->confirm('Do you wish to continue?')) {
                    break;
                }

                $this->allow($event);
            }

        }
    }

    public function allow(FileEvent $event)
    {
        $type               = $event->getType();
        $site               = $event->getSite($this->guardFile);
        $filePath           = $event->getPath();
        $relativeFilePath   = ltrim(str_replace($site->getPath(), '', $filePath), DIRECTORY_SEPARATOR);
        $quarantineFilePath = $site->quarantinePath($relativeFilePath);
        $backupFilePath     = $site->backupPath($relativeFilePath);
        $backupBasePath     = dirname($backupFilePath);
        $fileBasePath       = dirname($filePath);


        switch ($type) {
            case 'MOVED_TO':
            case 'CREATE':
            case 'MODIFY':
                if (is_file($quarantineFilePath)) {
                    if (!is_dir($backupBasePath)) {
                        mkdir($backupBasePath, 0755, true);
                    }
                    $this->fileSystem->copy($quarantineFilePath, $backupFilePath, true);
                    if (!is_dir($fileBasePath)) {
                        mkdir($fileBasePath, 0755, true);
                    }
                    $this->fileSystem->copy($quarantineFilePath, $filePath, true);
                    $this->fileSystem->remove($quarantineFilePath);
                    $this->outputInterface->writeln("Allowed {$type} on {$filePath}.");
                } else {
                    $this->error("Quarantined file {$quarantineFilePath} NOT found!");
                }

                break;
            case 'MOVED_FROM':
            case 'DELETE':

                if ($this->fileSystem->exists($quarantineFilePath)) {
                    $this->fileSystem->remove($quarantineFilePath);
                }

                if ($this->fileSystem->exists($backupFilePath)) {
                    $this->fileSystem->remove($backupFilePath);
                }

                if ($this->fileSystem->exists($filePath)) {
                    $this->fileSystem->remove($filePath);
                }

                $this->outputInterface->writeln("Allowed {$type} on {$filePath}.");

                break;
        }

        $this->eventsFile->removeEvent($event);
        $this->eventsFile->dump();

    }
}
