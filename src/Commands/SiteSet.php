<?php namespace Avram\Guard\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteSet extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('site:set')
            ->setDescription('Set variable for a site')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('name', InputArgument::REQUIRED, 'Site name'),
                    new InputArgument('variable', InputArgument::REQUIRED, 'Variable name to set (can be: name, path, email, types, excludes]'),
                    new InputArgument('value', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Variable value (or values for excludes, separated with space) to set'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $name     = $oldName = $input->getArgument('name');
        $variable = $input->getArgument('variable');
        $value    = $input->getArgument('value');

        $site = $this->guardFile->findSiteByName($name);
        if ($site == null) {
            $this->error("Site with name {$name} was not found. Use: guard site:list");
            exit(1);
        }

        $siteIndex = $this->guardFile->findSiteIndexByName($name);

        $allowed = ['name', 'path', 'types', 'email', 'excludes'];
        if (!in_array($variable, $allowed)) {
            $this->error("Invalid variable name: {$variable}, can be only one of: ".implode(', ', $allowed));
            exit(1);
        }

        switch ($variable) {
            case 'name':
                $site->setName($value[0]);
                break;
            case 'path':
                $site->setPath($value[0]);
                break;
            case 'types':
                $site->setTypes($value[0]);
                break;
            case 'email':
                $site->setEmail($value[0]);
                break;
            case 'excludes':
                $realPaths = [];
                foreach ($value as $path) {
                    $realPaths[] = file_exists($path) ? realpath($path) : $path;
                }
                $site->setExcludes($realPaths);
                break;
        }

        $this->guardFile->updateSite($site, $siteIndex);
        $this->guardFile->dump();

        $output->writeln("Option {$variable} is changed to [".implode(', ', $value)."] for site {$oldName}");

    }
}