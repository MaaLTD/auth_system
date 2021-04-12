<?php

namespace App;

class Helper
{
    public static function displayErrors(int $turn = 0)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', $turn);
        ini_set('display_startup_errors', $turn);
    }
}