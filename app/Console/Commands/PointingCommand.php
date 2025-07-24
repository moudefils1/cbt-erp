<?php

namespace App\Console\Commands;

use App\Enums\AbsenceStatusEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\LeaveEnum;
use App\Helpers\AppHelper;
use App\Models\Absence;
use App\Models\Employee;
use App\Models\EmployeeTraining;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Training;
use App\Settings\AppSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PointingCommand extends Command
{
    protected $signature = 'app:pointing';

    protected $description = 'Pointage des absences';

    public function __construct(public AppSettings $settings)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Debut de generation des pointages des employes');

        $workingDays = $this->settings->working_days_per_week;
        $this->info('Nombre de jours de travail par semaine: '.$workingDays);

        // Get today's date
        $today = Carbon::now();

        // Check if today is a working day (Monday to Friday or Saturday depending on settings)
        $isWorkingDay = AppHelper::isWorkingDay($today, $workingDays);

        if (! $isWorkingDay) {
            $this->error('Aujourd\'hui n\'est pas un jour de travail');

            return;
        }

        // Get all active employees
        $employees = Employee::where('status', EmployeeStatusEnum::WORKING)->get();
        $this->info('Nombre d\'employes: '.$employees->count());

        // Get today's holiday if any
        $holiday = Holiday::where('date', $today->format('Y-m-d'))->first();
        if ($holiday) {
            $this->info('Jour férié: '.$holiday->name);
        }

        // Get today's leaves
        $paidLeaves = Leave::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('status', LeaveEnum::APPROVED)
            ->whereHas('leaveType', function ($query) {
                $query->where('is_paid', true);
            }
            )
            ->get();
        $unpaidLeaves = Leave::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('status', LeaveEnum::APPROVED)
            ->whereHas('leaveType', function ($query) {
                $query->where('is_paid', false);
            }
            )
            ->get();
        // $this->info('Nombre de congés: ' . $leaves->count());

        // Get today's trainings
        $trainings = Training::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('status', true)
            ->get();
        $this->info('Nombre de formations: '.$trainings->count());

        // Get today's employee trainings
        $employeeTrainings = EmployeeTraining::whereHas('training', function ($query) use ($today) {
            $query->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->where('status', true);
        })->get();
        $this->info('Nombre de formations employés: '.$employeeTrainings->count());

        // Process each employee
        foreach ($employees as $employee) {
            $this->info('Traitement de l\'employé: '.$employee->first_name.' '.$employee->last_name);

            // Check if employee already has an absence record for today
            $existingAbsence = Absence::where('employee_id', $employee->id)
                ->where('date', $today->format('Y-m-d'))
                ->first();

            if ($existingAbsence) {
                $this->info('Un pointage existe déjà pour cet employé aujourd\'hui');

                continue;
            }

            // Check if today is a holiday
            if ($holiday) {
                $status = AbsenceStatusEnum::PRESENT;
                $this->info('L\'employé est en jour férié');
                Absence::create([
                    'employee_id' => $employee->id,
                    'date' => $today->format('Y-m-d'),
                    'is_present' => true,
                    'status' => $status,
                ]);

                continue;
            }

            // Determine absence status
            $status = AbsenceStatusEnum::PRESENT; // Default status
            $isPresent = true; // Default is present

            // Check if employee is on paid leave
            $employeePaidLeave = $paidLeaves->firstWhere('employee_id', $employee->id);
            if ($employeePaidLeave) {
                $status = AbsenceStatusEnum::VACATION;
                $this->info('L\'employé est en congé');
            }

            // Check if employee is on unpaid leave
            $employeeUnpaidLeave = $unpaidLeaves->firstWhere('employee_id', $employee->id);
            if ($employeeUnpaidLeave) {
                $status = AbsenceStatusEnum::ABSENT;
                $isPresent = false; // Not present
                $this->info('L\'employé est en congé sans solde');
            }

            // Check if employee is in training
            $employeeTraining = $employeeTrainings->firstWhere('employee_id', $employee->id);
            if ($employeeTraining) {
                $status = AbsenceStatusEnum::FORMATION;
                $this->info('L\'employé est en formation');
            }

            // Create absence record
            Absence::create([
                'employee_id' => $employee->id,
                'date' => $today->format('Y-m-d'),
                'is_present' => $isPresent,
                'status' => $status,
            ]);

            $this->info('Pointage créé avec le statut: '.$status->getLabel());
        }

        $this->info('Fin de generation des pointages des employes');
    }
}
