<?php
// login / password @ new.mcko.ru
$username = '';
$password = '';

$baseUrl    = 'https://new.mcko.ru';

$email =
    [
        'server' =>
        [
            'smtp' => 'smtp.gmail.com',
            'port'  => 465,
            'security' => 'ssl',
            'username' => '',
            'password' => '',
        ],
        'subject'  => 'Новые оценки в школе',
        'from'     => ['???@???.com' => 'Дневник'],
        'to'       => ['???@???.com']
    ];
