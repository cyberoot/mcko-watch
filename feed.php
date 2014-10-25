<?php
require 'config.php';
require_once 'lib/autoload.php';

$mrko = new MrkoWrapper($baseUrl, new RequestHandler('tmp/cookie.txt'));

$mrko->Login($username, $password, $token);

if(in_array('-weekly', $argv))
{
    $marks = $mrko->GetMarkbook();
    MarkReporter::SendWeeklyReport($marks, $email);
}
else
{
    $todayMarks = $mrko->GetMarksForToday();
    MarkReporter::SendReport($todayMarks, $email);
}

// $mrko->Logout();
