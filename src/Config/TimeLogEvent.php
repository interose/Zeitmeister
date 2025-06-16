<?php

namespace App\Config;

enum TimeLogEvent: string
{
    case CheckIn = 'checkin';
    case CheckOut = 'checkout';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
