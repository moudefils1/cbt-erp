<?php

use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateAppSettings extends SettingsMigration
{
    /**
     * @throws SettingAlreadyExists
     */
    public function up(): void
    {
        $this->migrator->add('app.name', 'Ocular');
        $this->migrator->add('app.description', 'Ocular is a system for managing the salary treatment of employees.');
        $this->migrator->add('app.logo', 'logo.png');
        $this->migrator->add('app.favicon', 'favicon.ico');
        $this->migrator->add('app.email', 'admin@app.com');
        $this->migrator->add('app.phone', '+1 (555) 123-4567');
        $this->migrator->add('app.address', '123 Main St, City, Country');
        $this->migrator->add('app.working_days_per_week', 5);
        $this->migrator->add('app.working_hours_per_day', 8);
    }
}
