<?php

class MarkReporter
{
    public static function PrepareMessage($marks)
    {
        $body = '';

        foreach ($marks as $mark)
        {
            if(!empty($mark['marks']))
            {
                $body .= "Урок {$mark['num']}\t{$mark['discipline']}\t\t" . implode(", ", $mark['marks']) . "\n";
            }
        }

        return $body;
    }

    public static function SendReport($dayMarks, $email)
    {
        $body = self::PrepareMessage(reset($dayMarks));
        if(empty($body))
        {
            return;
        }

        $dayKey = array_keys($dayMarks);
        $body = reset($dayKey) . "\n\n" . $body;

        $transport = Swift_SmtpTransport::newInstance($email['server']['smtp'], $email['server']['port'], $email['server']['security'])
                                        ->setUsername($email['server']['username'])
                                        ->setPassword($email['server']['password']);

        $mailer = Swift_Mailer::newInstance($transport);

        $message = Swift_Message::newInstance($email['subject'])
                                ->setCharset('utf-8')
                                ->setFrom($email['from'])
                                ->setTo($email['to'])
                                ->setBody($body);

        return $mailer->send($message);
    }
}
