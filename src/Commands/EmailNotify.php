<?php namespace Avram\Guard\Commands;

use Avram\Guard\FileEvent;
use Avram\Guard\Site;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EmailNotify extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('email:notify')
            ->setDescription('Send email notification(s) about changed files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $events = $this->eventsFile->getEvents();
        $sites  = [];

        if (empty($events)) {
            $output->writeln("No events to notify about!");
            exit(0);
        }

        /** @var FileEvent $event */
        foreach ($events as $event) {
            $site = $event->getSite($this->guardFile);
            $name = $site->getName();
            if (!isset($sites[$name])) {
                $sites[$name] = [];
            }

            if ($event->getStatus() != FileEvent::NOTIFIED) {
                $sites[$name][$event->getPath()] = $event->getType();
            }
        }


        /** @var Site $site */
        foreach ($sites as $name => $files) {
            $site  = $this->guardFile->findSiteByName($name);
            $email = $site->getEmail();

            if (empty($files)) {
                continue;
            }

            if (empty($email)) {
                $email = $this->guardFile->getEmail('address');
            }

            if (empty($email)) {
                $siteName = $site->getName();
                $output->writeln("Can't find email to send notification for site: {$siteName}");
                continue;
            }

            $output->write("Sending email to {$email}... ");
            $sent = $this->mailer->sendNotificationEmail($email, $site, $files);
            if ($sent) {
                $output->writeln("OK");
                $this->markAsNotified($files);
            } else {
                $output->writeln("FAILED");
            }
        }

    }

    private function markAsNotified(array $files)
    {
        foreach ($files as $file => $ev) {
            $event = $this->eventsFile->getFileEventByPath($file);
            $event->setStatus(FileEvent::NOTIFIED);
            $this->eventsFile->updateFileEvent($event);
        }
        $this->eventsFile->dump();
    }

}