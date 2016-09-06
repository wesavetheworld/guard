<?php

namespace Avram\Guard\Command;

use Avram\Guard\Site;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SiteAdd extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('site:add')
            ->setDescription('Add site to guard list.')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('name', InputArgument::REQUIRED),
                    new InputOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to guard', '.'),
                    new InputOption('types', 't', InputOption::VALUE_REQUIRED, 'File extensions to protect', '*.php;*.htm*;*.js;*.css;*.sql'),
                    new InputOption('email', 'e', InputOption::VALUE_REQUIRED, 'E-mail', null),
                    new InputOption('excludes', 'x', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Paths to exclude', []),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $name     = $input->getArgument('name');
        $path     = $input->getOption('path');
        $types    = $input->getOption('types');
        $email    = $input->getOption('email');
        $excludes = $input->getOption('excludes');

        /** @var Site $exists */
        $exists = $this->guardFile->findSiteByName($name);

        if ($exists) {
            $this->error("Site with name {$name} already exists! Please try another name.");
            exit(1);
        }

        /** @var Site $exists */
        $exists = $this->guardFile->findSiteByPath($path);

        if ($exists) {
            $existingName = $exists->getName();
            $this->error("Site on path {$path} already exists with name {$existingName}! Please try another path.");
            exit(1);
        }


        $site = new Site($name, $path, $types, $email, $excludes);
        $this->guardFile->addSite($site);
        $this->guardFile->dump();
        $output->writeln("Site {$name} added!");

    }
}