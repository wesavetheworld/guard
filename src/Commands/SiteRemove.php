<?php namespace Avram\Guard\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteRemove extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('site:remove')
            ->setDescription('Remove site from guarded list')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('name', InputArgument::REQUIRED, 'Name of the site to delete'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $name = $input->getArgument('name');

        $site = $this->guardFile->findSiteByName($name);
        if ($site === null) {
            $this->error("Site with name {$name} is not found. Use: guard site:list");
        }

        $output->writeln("Are you sure you want to stop guarding site {$name}?");
        if (!$this->confirm("This action can NOT be undone!")) {
            exit(0);
        }

        $this->fileSystem->remove($site->quarantinePath());
        $this->fileSystem->remove($site->backupPath());

        $this->guardFile->removeSite($site->getName());
        $this->guardFile->dump();

        $output->writeln("Site {$name} is successfully removed from guard list");
    }
}