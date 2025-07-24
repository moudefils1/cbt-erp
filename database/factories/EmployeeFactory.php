<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Grade;
use App\Models\Location;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Employee::class;

    public function definition()
    {
        $employeeTypeId = $this->faker->numberBetween(1, 4);

        return [
            'matricule' => $this->faker->unique()->bothify('??????-####'),
            'nni' => $this->faker->randomNumber(8, true),
            'cnps_no' => $this->faker->unique()->bothify('CNPS-#######'),
            'name' => $this->faker->firstName,
            'surname' => $this->faker->lastName,
            // 'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'gender' => $this->faker->randomElement([1, 2]), // 1 pour Homme, 2 pour Femme
            'nationality' => $this->faker->country,
            'birth_place' => $this->faker->city,
            'birth_date' => $this->faker->date(),
            'marital_status' => $this->faker->randomElement([1, 2]), // 1 pour Marié, 2 pour Célibataire
            'children_count' => $this->faker->numberBetween(0, 5),
            'emergency_contact_name' => $this->faker->name,
            'emergency_contact_phone' => $this->faker->phoneNumber,
            'emergency_contact_relationship' => $this->faker->word,
            'employee_type_id' => $employeeTypeId,
            'hiring_date' => $this->faker->date(),
            'end_date' => $employeeTypeId === 1 ? $this->faker->date() : null,
            'status' => 1, // 1 pour Actif
            'status_start_date' => $this->faker->date(),
            'status_end_date' => $this->faker->date(),
            'status_comment' => $this->faker->text,
            // 'on_leave' => $this->faker->boolean(),
            'created_by' => 1,
            'created_at' => now(),
        ];
    }

    //    public function configure()
    //    {
    //        return $this->afterCreating(function (Employee $employee) {
    //            // Associer un task, location et grade à cet employé
    //            $employee->update([
    //                'location_id' => Location::factory(),
    //                'task_id' => Task::factory(),
    //                'grade_id' => Grade::factory(),
    //            ]);
    //        });
    //    }
}
