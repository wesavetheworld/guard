<?php namespace Avram\Guard\Commands;

use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends BaseCommand
{
    const VERSION_URL = 'http://avramovic.github.io/guard/guard.version';

    const PHAR_URL = 'http://avramovic.github.io/guard/guard.phar';

    const FILE_NAME = 'guard.phar';

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $version;

    public function configure()
    {
        $this
            ->setName('update')
            ->setDescription('Update guard to latest version')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('rollback', 'R', InputOption::VALUE_NONE, 'Rollback to previous version'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = new Updater(null, false);
        $updater->getStrategy()->setPharUrl(self::PHAR_URL);
        $updater->getStrategy()->setVersionUrl(self::VERSION_URL);

        if ($input->getOption('rollback')) {
            $updater->rollback();
            $output->writeln('Rolled back to previous version.');
            $this->call('', ['--version']);
            exit(0);
        }

        try {
            $result = $updater->update();
            if ($result) {
                $new = $updater->getNewVersion();
                $old = $updater->getOldVersion();
                $output->writeln(sprintf(
                    'Updated from SHA-1 %s to SHA-1 %s', $old, $new
                ));
            } else {
                $output->writeln('No update needed!');
            }
        } catch (\Exception $e) {
            $output->writeln('Well, something happened! Either an oopsie or something involving hackers.');
        }
    }
}