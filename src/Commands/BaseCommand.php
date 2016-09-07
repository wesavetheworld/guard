<?php namespace Avram\Guard\Commands;

use Avram\Guard\Exceptions\GuardFileException;
use Avram\Guard\Services\GuardFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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

    public function table(array $headers, array $rows)
    {
        $table = new Table($this->outputInterface);
        $table
            ->setHeaders($headers)
            ->setRows($rows);
        $table->render();
    }

    public function confirm($question)
    {
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion($question." [y/N]\n", false);
        return $helper->ask($this->inputInterface, $this->outputInterface, $question);
    }
}