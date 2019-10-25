<?php

namespace App\Utils;

class StringUtils 
{
    public static function generateRandomCode($length) {
        $result = '';
        for($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }
        return $result;
    }
}