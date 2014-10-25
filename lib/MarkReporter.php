<?php

class MarkReporter
{
    public static function SendReport($dayMarks, $email)
    {
        $body = self::PrepareMessage(reset($dayMarks));
        if (empty($body))
        {
            return;
        }

        $dayKey = array_keys($dayMarks);
        $body   = reset($dayKey) . "\n\n" . $body;

        $transport = Swift_SmtpTransport::newInstance(
            $email['server']['smtp'],
            $email['server']['port'],
            $email['server']['security']
        )
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

    public static function PrepareMessage($marks, $template = false)
    {
        $body = '';

        $template = $template ?: "Урок %1\$d\t%2\$s\t\t%4\$s" . PHP_EOL;

        foreach ($marks as $mark)
        {
            if (!empty($mark['marks']))
            {
                $body .= sprintf($template, $mark['num'], $mark['discipline'], $mark['hometask'], implode(", ", $mark['marks']));
            }
        }

        return $body;
    }

    public static function SendWeeklyReport($weekMarks, $email)
    {
        setlocale(LC_TIME, 'ru_RU.UTF-8');

        $tplPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'email-weekly.html';
        $emailTpl = file_get_contents($tplPath);

        $body = '';
        $result = null;

        foreach ($weekMarks as $dayKey => $dayMarks)
        {
            $dayBody = self::PrepareMessage($dayMarks, "<tr> <td width='20' valign='top'>%s</td> <td width='150' valign='top'>%s</td> <td width='250' valign='top'>%s</td> <td width='150' valign='top'>%s</td> </tr>\n");
            if (empty($dayBody))
            {
                continue;
            }

            $stamp = strtotime($dayKey . '.' . date('Y'));
            $body .= "<tr><th colspan='4'><strong>" . strftime('%A %x', $stamp) . "</strong></th></tr>\n" . $dayBody;
        }

        if (!empty($body))
        {
            $body = str_ireplace('#MARKS#', $body, $emailTpl);
            $body = str_ireplace('#SUBJECT#', $email['subject-weekly'], $body);

            $transport = Swift_SmtpTransport::newInstance(
                $email['server']['smtp'],
                $email['server']['port'],
                $email['server']['security']
            )
            ->setUsername($email['server']['username'])
            ->setPassword($email['server']['password']);

            $mailer = Swift_Mailer::newInstance($transport);

            $message = Swift_Message::newInstance($email['subject-weekly'])
                                    ->setCharset('utf-8')
                                    ->setFrom($email['from'])
                                    ->setTo($email['to'])
                                    ->setBody($body, 'text/html');

            $result = $mailer->send($message);
        }

        setlocale(LC_TIME, null);

        return $result;
    }
}
