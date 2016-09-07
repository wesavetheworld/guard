<?php namespace Avram\Guard\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EmailSet extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('email:set')
            ->setDescription('Set email configuration')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('variable', InputArgument::REQUIRED, 'Variable name to set (can be: recipient, transport, sendmail, smtp_host, smtp_port, smtp_user or smtp_pass)'),
                    new InputArgument('value', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Variable value to set'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $variable = $input->getArgument('variable');
        $value    = $input->getArgument('value');

        $allowed = ['address', 'transport', 'sendmail', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass'];
        if (!in_array($variable, $allowed)) {
            $this->error("Invalid variable name: {$variable}, can be only one of: ".implode(', ', $allowed));
        }

        if ($variable == 'transport') {
            $allowed = ['mail', 'sendmail', 'smtp'];
            if (!in_array($value[0], $allowed)) {
                $this->error("Invalid transport value: {$value[0]}, can be only one of: ".implode(', ', $allowed));
            }
        }

        $old = $this->guardFile->getEmail($variable);
        $new = implode(', ', $value);
        if (!$this->confirm("Are you sure you want to change {$variable} from [{$old}] to [{$new}]?")) {
            exit(0);
        }

        $this->guardFile->setEmail($variable, implode(',', $value));
        $this->guardFile->dump();

        $output->writeln("Option {$variable} is changed to [".implode(', ', $value)."] for global email settings.");

    }
}