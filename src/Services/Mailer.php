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
                <h3>Guard service</h3>
            </div>
            <div>
                {$body}
                <p><small>Please do NOT reply to this message! Your reply will not be seen by anyone.</small></p>
            </div>
        </div>
        </div>
    <div style="margin-top: 30px; text-align: center">
        <p>
            <small><a href="https://avramovic.github.io/guard" target="_blank">Guard</a></small>
        </p>
        </div>
    </div>
</body>
</html>
HTML;


        $message->setBody($html, 'text/html');
        $message->addPart(strip_tags($body).PHP_EOL.PHP_EOL.'Please do NOT reply to this message! Your reply will not be seen by anyone.', 'text/plain');
    }

    protected function getFromAddress(Site $site = null)
    {
        if (empty($site)) {
            return $this->guardFile->getEmail('address');
        }

        $ip = gethostbyname($site->getName());
        if ($ip == $site->getName()) {
            return $this->guardFile->getEmail('address');
        }

        return 'guard-notifications@'.$site->getName();
    }

    public function sendTestEmail($to, $from = null)
    {
        $message = \Swift_Message::newInstance("Guard test email")
            ->setFrom($from ? $from : $to)
            ->setTo($to);

        $this->addBody($message, '<p>If you are reading this then Guard is working and can send emails from your server!</p>');

        return $this->mailer->send($message);
    }

    public function sendNotificationEmail($email, Site $site, array $files)
    {
        $message = \Swift_Message::newInstance("Guard file tampering notification")
            ->setFrom($this->getFromAddress($site))
            ->setTo($email);

        $siteName = $site->getName();

        $body = "<p>We are notifying you that something tried to tamper with one or more files on your site <strong>{$siteName}</strong></p>\n";
        $body .= "<p>These file events were blocked so far:</p>\n";

        $body .= "<table border='1' cellspacing='0' cellpadding='2'>\n";
        $body .= "<tr><td><strong>Path</strong></td> <td><strong>Event</strong></td></tr>\n";

        foreach ($files as $path => $event) {
            $body .= "<tr><td>{$path}</td> <td>{$event}</td></tr>\n";
        }

        $body .= "</table>\n";
        $body .= "<p>Please SSH to your server and review these events by using following commands:<br/>\n";
        $body .= "<code>guard event:list</code> to show blocked events<br/>\n";
        $body .= "<code>guard event:diff [id]</code> to show modifications<br/>\n";
        $body .= "<code>guard event:allow [id|all]</code> to allow event(s) to occur<br/>\n";
        $body .= "<code>guard event:remove [id|all]</code> to forget event(s) and remove them<br/>\n";
        $body .= "</p>\n";


        $this->addBody($message, $body);

        return $this->mailer->send($message);
    }
}