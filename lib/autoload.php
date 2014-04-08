<?php

require_once 'Swift/lib/swift_required.php';

function autoload($className)
{
    set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ .'/');
    if(file_exists((__DIR__  . '/' . $className . '.php')))
    {
        require_once __DIR__ . '/' . $className . '.php';
    }
    else
    {
        spl_autoload($className);
    }
}

spl_autoload_extensions('.php, .class.php');
spl_autoload_register('autoload');

