<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class truncateDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateData();
    }

    public function truncateData()
    {
        $this->command->info('Effacement des données de la base de données en cours...');

        // Get all table names except 'users'
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        $tables = array_filter($tables, function ($table) {
            return $table !== 'users';
        });

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate each table
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Effacement des données de la base de données terminé.');
    }
}
