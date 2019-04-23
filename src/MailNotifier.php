<?php

namespace Glas;

use Symfony\Component\Yaml\Yaml;

class MailNotifier
{
    /** @var \Swift_Mailer */
    private $mailer;

    /** @var string */
    private $recipient;

    public function __construct()
    {
        $config = Yaml::parseFile(__DIR__.'/../config.yml');

        $this->mailer = new \Swift_Mailer(new \Swift_SmtpTransport(
            $config['config']['mail']['host'],
            $config['config']['mail']['port']
        ));

        $this->sender = $config['config']['mail']['sender'];
        $this->recipient = $config['config']['mail']['recipient'];
    }

    public function notify(Check $check): void
    {
        $message = sprintf(
            'Le service "%s" est maintenant %s',
            $check->getApplication(),
            $check->isUp() ? 'UP' : 'DOWN'
        );

        $mail = (new \Swift_Message($message))
            ->setFrom($this->sender)
            ->setTo($this->recipient)
            ->setBody($message)
            ;

        $this->mailer->send($mail);
    }
}
