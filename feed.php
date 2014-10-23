<?php
require 'config.php';
require_once 'lib/autoload.php';

$mrko = new MrkoWrapper($baseUrl, new RequestHandler('tmp/cookie.txt'));


$mrko->Login($username, $password, $token);

$todayMarks = $mrko->GetMarksForToday();

// $mcko->Logout();

MarkReporter::SendReport($todayMarks, $email);
