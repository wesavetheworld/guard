<?php namespace Avram\Guard\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExcludeAdd extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('exclude:add')
            ->setDescription('Add exclude for a site')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('name', InputArgument::REQUIRED, 'Site name'),
                    new InputArgument('excludes', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Path (or paths, separated by space) to exclude from watching'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $name     = $oldName = $input->getArgument('name');
        $excludes = $input->getArgument('excludes');

        $site = $this->guardFile->findSiteByName($name);
        if ($site == null) {
            $this->error("Site with name {$name} was not found. Use: php guard.phar site:list");
        }

        $siteIndex = $this->guardFile->findSiteIndexByName($name);

        foreach ($excludes as $path) {

            $path      = file_exists($path) ? realpath($path) : $path;
            $siteCheck = $this->guardFile->findSiteByLongPath($path);
            if (!$siteCheck || ($siteCheck->getPath() != $site->getPath())) {
                $this->error("{$path} does not belong to site {$name}.");
            }

            $existing = $site->getExcludes();
            if (in_array($path, $existing)) {
                $output->writeln("{$path} was already excluded.");
            } else {
                $site->addExclude($path);
                $output->writeln("{$path} is now excluded.");
            }
        }

        $this->guardFile->updateSite($site, $siteIndex);
        $this->guardFile->dump();


    }
}