<?php

if (! function_exists('swap')) {
    function swap(&$var1, &$var2)
    {
        list($var1, $var2) = [$var2, $var1];
    }
}

if (! function_exists('error')) {
    function error($message)
    {
        trigger_error($message, E_USER_ERROR);
    }
}

if (! function_exists('warning')) {
    function warning($message)
    {
        trigger_error($message, E_USER_WARNING);
    }
}

if (! function_exists('notice')) {
    function notice($message)
    {
        trigger_error($message, E_USER_NOTICE);
    }
}
