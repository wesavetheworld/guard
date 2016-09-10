<?php namespace Avram\Guard\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteBackup extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('site:backup')
            ->setDescription('Backup watched files to safe location')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('name', InputArgument::REQUIRED, 'Name of the site to backup'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $name = $input->getArgument('name');
        $site = $this->guardFile->findSiteByName($name);

        if ($site === null) {
            $this->error("Site with name {$name} is not found. Use: php guard.phar site:list");
        }

        $path       = $site->getPath();
        $backupPath = $site->backupPath();

        if ($this->fileSystem->exists($backupPath)) {
            $this->fileSystem->remove($backupPath);
        }

        $output->writeln("Backing up all files from {$path} to {$backupPath}");
        $output->writeln("Depending on the folder size, this may take a while...");

        $this->fileSystem->mirror($path, $backupPath);

        $output->writeln("All files are backed up!");
    }
}