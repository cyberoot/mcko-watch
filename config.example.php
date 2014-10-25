<?php
// login / password @ mrko.mos.ru + token from pgu.mos.ru
$username = '';
$password = '';
$token    = '';

$baseUrl = 'https://mrko.mos.ru';

$email =
    [
        'server'         =>
            [
                'smtp'     => 'smtp.gmail.com',
                'port'     => 465,
                'security' => 'ssl',
                'username' => '',
                'password' => '',
            ],
        'subject'        => 'Новые оценки в школе',
        'subject-weekly' => 'Оценки в школе за неделю',
        'from'           => ['???@???.com' => 'Дневник'],
        'to'             => ['???@???.com']
    ];
