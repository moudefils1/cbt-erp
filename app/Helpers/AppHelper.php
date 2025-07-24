<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class AppHelper
{
    public static function isWorkingDay(Carbon $date, int $workingDays)
    {
        return $date->dayOfWeek >= Carbon::MONDAY &&
               $date->dayOfWeek <= ($workingDays == 6 ? Carbon::SATURDAY : Carbon::FRIDAY);
    }
}
