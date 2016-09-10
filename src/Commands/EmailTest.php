<?php namespace Avram\Guard\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EmailTest extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('email:test')
            ->setDescription('Send test email')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('email', InputArgument::OPTIONAL, 'Email address to send test message to', null),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $email = $input->getArgument('email');
        if ($email === null) {
            $email = $this->guardFile->getEmail('address');
        }

        if (empty($email)) {
            $this->error("No recipient email address defined. Use: php guard.phar email:set address [email-address]");
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) != $email) {
            $this->error("{$email} does not seem like valid email address!");
        }

        $transport = $this->guardFile->getEmail('transport');

        $output->writeln("Sending email to {$email} using {$transport} transport...");

        $sent = $this->mailer->sendTestEmail($email);
        if ($sent) {
            $output->writeln("Test email sent. Please check your email at {$email}");
        } else {
            $this->error("Couldn't send email. Please check your config with: guard email:show");
        }
    }
}