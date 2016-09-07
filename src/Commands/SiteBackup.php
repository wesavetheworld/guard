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
            $this->error("Site with name {$name} is not found. Use: guard site:list");
        }

        $backuppath = $site->backupPath();

        if (is_dir($backuppath)) {
            rmdir_recursive($backuppath);
        }

        if (!is_dir($backuppath)) {
            mkdir($backuppath, 0755, true);
        }
        $exts    = $site->getTypes();
        $extsArr = glob2arr($exts);

        $path = $site->getPath();
        $output->writeln("Backing up files matching {$exts} from {$path} to {$backuppath}");
        $output->writeln("Depending on the site size, this may take a while...");

        foreach ($extsArr as $ext) {
            system(sprintf("rsync -a --include '*/' --include '%s' --exclude '*' %s %s", $ext, $path.'/.', $backuppath));
        }
        $output->writeln("All files mathing {$exts} are backed up!");
    }
}