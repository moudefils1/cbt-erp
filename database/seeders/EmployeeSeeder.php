<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Grade;
use App\Models\Location;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Demander une confirmation avant de nettoyer la base de données
        $confirmation = $this->command->ask('Êtes-vous sûr de vouloir supprimer toutes les données et recommencer ? (y/n)', 'n');

        if (strtolower($confirmation) === 'y' || strtolower($confirmation) === 'yes') {
            $this->cleanUpData();
        }

        // Créer les données après nettoyage
        $this->createEmployeeData();
    }

    // Fonction pour nettoyer les données
    protected function cleanUpData(): void
    {
        $this->command->info('Suppression des données en cours...');

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Supprimer les relations d'abord (task dépend de location, employee dépend de task et location)
        DB::table('tasks')->delete();
        DB::table('employees')->delete();
        DB::table('locations')->delete();
        DB::table('grades')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $this->command->info('Les données ont été nettoyées avec succès.');
    }

    // Fonction pour créer des employés
    protected function createEmployeeData(): void
    {
        $count = (int) $this->command->ask('Combien de personnels voulez-vous ajouter ?', 5);

        for ($i = 0; $i < $count; $i++) {
            $location = Location::factory()->create();
            $task = Task::factory()->create(['location_id' => $location->id]);
            $grade = Grade::factory()->create();

            Employee::factory()->create([
                'location_id' => $location->id,
                'task_id' => $task->id,
                'grade_id' => $grade->id,
            ]);
        }

        $this->command->info("{$count} personnels ont été ajoutés avec succès !");
    }
}
