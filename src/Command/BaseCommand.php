<?php namespace Avram\Guard\Command;

use Avram\Guard\Exceptions\GuardFileException;
use Avram\Guard\Service\GuardFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    /** @var InputInterface */
    protected $inputInterface;

    /** @var OutputInterface */
    protected $outputInterface;

    /** @var GuardFile */
    public $guardFile;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->inputInterface  = $input;
        $this->outputInterface = $output;

        $this->guardFile = null;

        try {
            $this->guardFile = new GuardFile();
        } catch (GuardFileException $ex) {
            $output->writeln($ex->getMessage());
            exit(1);
        }
    }

    public function error($message, $title = "Error!")
    {
        $formatter      = $this->getHelper('formatter');
        $errorMessages  = array($title, $message);
        $formattedBlock = $formatter->formatBlock($errorMessages, 'error', true);
        $this->outputInterface->writeln($formattedBlock);
    }
}