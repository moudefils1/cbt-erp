<?php

namespace App\Console\Commands;

use App\Actions\SalaryTreatmentAction;
use App\Enums\EmployeeStatusEnum;
use App\Models\Employee;
use Illuminate\Console\Command;

class SalaryTreatmentCommand extends Command
{
    protected $signature = 'app:salary';

    protected $description = 'Generate salary treatment on every salary_treatment_day of the week';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        // Check if today is an end of month
        if (now()->isLastOfMonth()) {
            $this->info('Today is the end of the month. Salary treatment will be processed.');
        } else {
            $this->info('Today is not the end of the month. Salary treatment will not be processed.');
            return;
        }

        // Get all active employees
        $employees = Employee::where('status', EmployeeStatusEnum::WORKING)->get();
        $this->info("Processing salaries for {$employees->count()} active employees.");

        foreach ($employees as $employee) {

            $this->info("Processing salary for employee {$employee->name}...");

            (new SalaryTreatmentAction($employee))->handle();
        }

        $this->info('Salary treatment completed successfully.');
    }
}
