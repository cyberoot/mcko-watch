<?php

require 'config.php';
require_once 'lib/autoload.php';

$mcko = new MckoWrapper($baseUrl, new RequestHandler('tmp/cookie.txt'));

$mcko->Login($username, $password);

$todayMarks = $mcko->GetMarksForToday();

// $mcko->Logout();

MarkReporter::SendReport($todayMarks, $email);
