<?php namespace Avram\Guard\Commands;

use Avram\Guard\Site;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteList extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('site:list')
            ->setDescription('List guarded sites');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $sites = $this->guardFile->getSites();

        if (empty($sites)) {
            $output->writeln("No guarded sites");
            exit(0);
        }

        $header    = array('Name', 'Path', 'Types', 'Email', 'Excludes');
        $tableRows = [];

        /** @var Site $site */
        foreach ($sites as $site) {
            $row   = [];
            $row[] = $site->getName();
            $row[] = $site->getPath();
            $row[] = $site->getTypes();
            $row[] = $site->getEmail();

            $excludes  = $site->getExcludes();
            $shortened = [];
            foreach ($excludes as $excluded) {
                $shortened[] = str_replace($site->getPath(), '.', $excluded);
            }

            $row[] = implode(', ', $shortened);

            $tableRows[] = $row;
        }

        $this->table($header, $tableRows);
    }
}