<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AppSettings extends Settings
{
    /**
     * The name of the instance who uses this system
     */
    public string $name;

    /**
     * The description of the instance who uses this system
     */
    public string $description;

    /**
     * The logo of the instance that uses this system
     */
    public ?string $logo;

    /**
     * The email of the instance that uses this system
     */
    public string $email;

    /**
     * The phone of the instance that uses this system
     */
    public string $phone;

    /**
     * The address of the instance that uses this system
     */
    public string $address;

    /**
     * The working days per week, for example, 5 for Monday to Friday
     */
    public int $working_days_per_week;

    /**
     * The working hours per day, for example, 8 for 8 hours a day
     */
    public int $working_hours_per_day;

    /**
     * Get the settings group name
     */
    public static function group(): string
    {
        return 'app';
    }
}
