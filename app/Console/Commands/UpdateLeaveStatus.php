<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateLeaveStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-leave-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $this->info('Mise à jour des états de congés...');

        // Get approved leaves
        $leaves = \App\Models\Leave::where('status', \App\Enums\LeaveEnum::APPROVED)
            ->get();

        $inProgressCount = 0;
        $completedCount = 0;
        $inProgressIds = [];
        $completedIds = [];

        foreach ($leaves as $leave) {
            $startDate = $leave->start_date;
            $endDate = $leave->end_date;

            // Set end date to end of day to include the full last day
            $endDateEndOfDay = Carbon::parse($endDate)->endOfDay();

            // Leave has started but not completed
            if ($startDate->lte($now) && $endDateEndOfDay->gte($now)) {
                $leave->state = \App\Enums\StateEnum::IN_PROGRESS;
                $inProgressCount++;
                $inProgressIds[] = $leave->id;

                // Ensure employee is marked as on leave
                $leave->employee()->update([
                    'on_leave' => true,
                    'status' => \App\Enums\EmployeeStatusEnum::ON_LEAVE,
                ]);
            }
            // Leave has completed
            elseif ($endDateEndOfDay->lt($now)) {
                $leave->state = \App\Enums\StateEnum::COMPLETED;
                $completedCount++;
                $completedIds[] = $leave->id;

                // Ensure employee is no longer marked as on leave
                $leave->employee()->update([
                    'on_leave' => false,
                    'status' => \App\Enums\EmployeeStatusEnum::WORKING,
                ]);
            }

            $leave->save();
        }

        $inProgressIdsStr = empty($inProgressIds) ? 'aucun' : implode(', ', $inProgressIds);
        $completedIdsStr = empty($completedIds) ? 'aucun' : implode(', ', $completedIds);

        $this->info("États de congés mis à jour : $inProgressCount en cours (IDs: $inProgressIdsStr), $completedCount terminés (IDs: $completedIdsStr)");
        // Log the message
        Log::info("États de congés mis à jour : $inProgressCount en cours (IDs: $inProgressIdsStr), $completedCount terminés (IDs: $completedIdsStr)");
    }
}
