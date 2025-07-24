<?php

namespace App\Console\Commands;

use App\Enums\StateEnum;
use App\Models\Intern;
use App\Models\InternItem;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateInternStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-intern-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the status of interns and intern items based on their start and end dates.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->toDateString();
        $updatedCounts = ['interns' => 0, 'items' => 0];

        // STANDBY -> IN_PROGRESS
        $updatedCounts['interns'] += Intern::where('status', StateEnum::STANDBY)
            ->whereDate('internship_start_date', '<=', $today)
            ->update(['status' => StateEnum::IN_PROGRESS]);

        $updatedCounts['items'] += InternItem::where('status', StateEnum::STANDBY)
            ->whereDate('start_date', '<=', $today)
            ->update(['status' => StateEnum::IN_PROGRESS]);

        // IN_PROGRESS -> COMPLETED
        $updatedCounts['interns'] += Intern::where('status', StateEnum::IN_PROGRESS)
            ->whereDate('internship_end_date', '<', $today)
            ->update(['status' => StateEnum::COMPLETED]);

        $updatedCounts['items'] += InternItem::where('status', StateEnum::IN_PROGRESS)
            ->whereDate('end_date', '<', $today)
            ->update(['status' => StateEnum::COMPLETED]);

        // display and log the message
        if ($updatedCounts['interns'] > 0 || $updatedCounts['items'] > 0) {
            $message = "{$updatedCounts['interns']} stagiaires et {$updatedCounts['items']} affectations mis à jour.";
            $this->info($message);
            Log::info($message);
        } else {
            $this->info('Aucune mise à jour nécessaire.');
            Log::info('Aucune mise à jour nécessaire.');
        }

        return Command::SUCCESS;
    }
}
