<?php

namespace App\Actions;

use App\Enums\SalaryDeductionTypeEnum;
use App\Enums\StatusEnum;
use App\Helpers\AppHelper;
use App\Models\Absence;
use App\Models\Employee;
use App\Models\SalaryBonus;
use App\Models\SalaryDeduction;
use App\Models\TreatedSalary;
use App\Settings\AppSettings;
use Illuminate\Support\Facades\Log;

class SalaryTreatmentAction
{
    protected $startDate;

    protected $endDate;

    protected $workingHoursPerDay;

    protected $workingDaysPerWeek;

    public function __construct(public Employee $employee)
    {
        $setting = app(AppSettings::class);
        $this->startDate = now()->startOfMonth();
        $this->endDate = now()->endOfMonth();
        $this->workingHoursPerDay = $setting->working_hours_per_day;
        $this->workingDaysPerWeek = $setting->working_days_per_week;
    }

    public function handle(): void
    {
        try {
            // Get employee's attendance records for the current month
            $absences = Absence::where('employee_id', $this->employee->id)
                ->whereBetween('date', [$this->startDate, $this->endDate])
                ->get();

            // Calculate total working days in the month (excluding weekends)
            $totalWorkingDays = 0;
            $currentDate = $this->startDate->copy();

            while ($currentDate <= $this->endDate) {
                if (AppHelper::isWorkingDay($currentDate, $this->workingDaysPerWeek)) {
                    $totalWorkingDays++;
                }
                $currentDate->addDay();
            }

            // Calculate actual working days and hours
            $actualWorkingDays = $absences->where('is_present', true)->count();
            $totalWorkingHours = $totalWorkingDays * $this->workingHoursPerDay;
            $actualWorkingHours = $actualWorkingDays * $this->workingHoursPerDay;

            // check if the month ends on 30 or 31
            if ($this->endDate->day == 30) {
                $actualWorkingHours = $actualWorkingHours + $this->workingHoursPerDay;
            }

            if ($actualWorkingHours > $totalWorkingHours) {
                $actualWorkingHours = $totalWorkingHours;
            }

            // Calculate hourly rate and base salary
            $hourlyRate = $this->employee->basic_salary / $totalWorkingHours;
            $baseSalary = $hourlyRate * $actualWorkingHours;

            // Get active bonuses and deductions
            $bonuses = SalaryBonus::where('employee_id', $this->employee->id)
                ->where('status', StatusEnum::ACTIVE)
                ->get();

            $deductions = SalaryDeduction::all();

            // Calculate total bonuses
            $totalBonuses = 0;
            $bonusDetails = [];
            foreach ($bonuses as $bonus) {
                $bonusAmount = $bonus->amount;

                $totalBonuses += $bonusAmount;
                $bonusDetails[] = [
                    'name' => $bonus->name,
                    'amount' => $bonusAmount,
                ];
            }

            $subSalary = $baseSalary + $totalBonuses;

            // Calculate total deductions
            $totalDeductions = 0;
            $deductionDetails = [];
            foreach ($deductions as $deduction) {
                $deductionAmount = $deduction->value;
                if ($deduction->type->is(SalaryDeductionTypeEnum::PERCENTAGE)) {
                    $deductionAmount = ($subSalary * $deduction->value) / 100;
                }
                $totalDeductions += $deductionAmount;
                $deductionDetails[] = [
                    'name' => $deduction->name,
                    'amount' => $deductionAmount,
                    'type' => $deduction->type->getLabel(),
                    'rate' => $deduction->type->is(SalaryDeductionTypeEnum::PERCENTAGE) ? $deduction->value.'%' : number_format($deduction->value),
                ];
            }

            // get recoveries for this month
            $recoveries = $this->employee->debtItems()
                ->whereBetween('paid_at', [$this->startDate, $this->endDate])
                ->sum('paid_amount');

            // Calculate final salary
            $finalSalary = $subSalary - $totalDeductions - $recoveries;

            // Store the treated salary
            $data = [
                'employee_id' => $this->employee->id,
                'treatment_date' => now(),
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'total_working_days' => $totalWorkingDays,
                'actual_working_days' => $actualWorkingDays,
                'total_working_hours' => $totalWorkingHours,
                'actual_working_hours' => $actualWorkingHours,
                'hourly_rate' => $hourlyRate,
                'base_salary' => $baseSalary,
                'total_bonuses' => $totalBonuses,
                'total_deductions' => $totalDeductions,
                'final_salary' => $finalSalary,
                'bonus_details' => $bonusDetails,
                'deduction_details' => $deductionDetails,
                'notes' => "Salary treatment for {$this->startDate->format('F Y')}",
                'is_paid' => false,
                'created_by' => 1, // System user
                'total_recoveries' => $recoveries,
            ];

            $treatedSalary = TreatedSalary::where('employee_id', $this->employee->id)
                ->whereDate('start_date', $this->startDate)
                ->whereDate('end_date', $this->endDate)
                ->first();

            if ($treatedSalary) {
                $treatedSalary->update($data);
            } else {
                TreatedSalary::create($data);
            }

            Log::info("Processed salary for employee {$this->employee->name}:");
            Log::info("- Total working days: {$totalWorkingDays}");
            Log::info("- Actual working days: {$actualWorkingDays}");
            Log::info("- Total working hours: {$totalWorkingHours}");
            Log::info("- Actual working hours: {$actualWorkingHours}");
            Log::info("- Hourly rate: {$hourlyRate}");
            Log::info("- Base salary: {$baseSalary}");
            Log::info("- Total bonuses: {$totalBonuses}");
            Log::info("- Total deductions: {$totalDeductions}");
            Log::info("- Final salary: {$finalSalary}");

        } catch (\Exception $e) {
            Log::error("Error processing salary for employee {$this->employee->name}: ".$e->getMessage());
        }
    }
}
