<?php namespace Avram\Guard\Commands;

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends BaseCommand
{
    const MANIFEST_FILE = 'http://avramovic.github.io/guard/manifest.json';

    protected function configure()
    {
        $this
            ->setName('update')
            ->setDescription('Updates Guard to the latest version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager(Manifest::loadFile(self::MANIFEST_FILE));
        $manager->update($this->getApplication()->getVersion(), true);
    }
}