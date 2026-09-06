<?php

namespace App\Support;

class Money
{
    public static function format(int|float|string|null $amount): string
    {
        return number_format((float) $amount, 0, ',', ' ').' FCFA';
    }
}
