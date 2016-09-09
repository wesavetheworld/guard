<?php namespace Avram\Guard\Services;

use Avram\Guard\Site;

class Mailer
{
    /** @var GuardFile $guardFile */
    protected $guardFile;

    /** @var \stdClass $emailConf */
    protected $emailConf;

    /** @var \Swift_Mailer */
    protected $mailer;

    public function __construct(GuardFile $guardFile)
    {
        $this->guardFile = $guardFile;
        $this->emailConf = $this->guardFile->getEmail();
        $this->setupTransport();
    }

    protected function setupTransport()
    {
        switch ($this->emailConf->transport) {
            case 'sendmail':
                $this->mailer = \Swift_Mailer::newInstance(\Swift_SendmailTransport::newInstance($this->emailConf->sendmail));
                break;
            case 'smtp':
                $transport = \Swift_SmtpTransport::newInstance($this->emailConf->smtp_host, (int)$this->emailConf->smtp_port)
                    ->setUsername($this->emailConf->smtp_user)
                    ->setPassword($this->emailConf->smtp_pass);

                if (!empty($this->emailConf->smtp_encrypt)) {
                    $transport->setEncryption($this->emailConf->smtp_encrypt);
                }

                $this->mailer = \Swift_Mailer::newInstance($transport);
                break;
            default:
                $this->mailer = \Swift_Mailer::newInstance(\Swift_MailTransport::newInstance());
        }
    }

    public function error()
    {
//        $this->mailer->err
    }

    public function addBody(\Swift_Message &$message, $body)
    {
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="background-color: #eee; font-family: 'Open Sans', sans-serif;" bgcolor="#eee">
<div>
    <div style="padding: 30px 30px 10px; margin: 30px; background-color: white; border-radius: 5px; box-shadow: 0 1px 5px 0px #ccc;">
        <div>
            <div style="text-align: center">
                <h3>PHP Guard service</h3>
            </div>
            <div>
                {$body}
            </div>
        </div>
        </div>
    <div style="margin-top: 30px; text-align: center">
        <p>
            <small><a href="https://avramovic.github.io/php-guard" target="_blank">PHP Guard</a></small>
        </p>
        </div>
    </div>
</body>
</html>
HTML;


        $message->setBody($html, 'text/html');
        $message->addPart(strip_tags($body), 'text/plain');
    }

    protected function getFromAddress(Site $site = null)
    {
        if (empty($site)) {
            return $this->guardFile->getEmail('address');
        }

        $ip = gethostbyname($site->getName());
        if ($ip == $site->getName()) {
            return 'php-guard@avramovic.github.io';
        }

        return 'php-guard@'.$site->getName();
    }

    public function sendTestEmail($to, $from = null)
    {
        /** @var Site $site */
        $message = \Swift_Message::newInstance("PHP Guard test email")
            ->setFrom($from ? $from : $to)
            ->setTo($to);

        $this->addBody($message, '<p>If you are reading this then Guard is working and can send emails from your server!</p><p>Please do NOT reply to this e-mail!</p>');

        return $this->mailer->send($message);
    }
}