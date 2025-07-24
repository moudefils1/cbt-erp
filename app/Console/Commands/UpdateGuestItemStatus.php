<?php

namespace App\Console\Commands;

use App\Enums\ApprovalEnum;
use App\Enums\StateEnum;
use App\Models\GuestItem;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateGuestItemStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-guest-item-status';

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
        $today = Carbon::now()->toDateString();

        // STANDBY -> IN_PROGRESS (for items that have started but not yet in progress)
        $updatedToInProgress = GuestItem::where('state', StateEnum::STANDBY)
            ->where('start_date', '<=', $today)
            ->update(['state' => StateEnum::IN_PROGRESS]);

        // IN_PROGRESS -> COMPLETED (for items that have ended but not yet completed)
        $updatedToCompleted = GuestItem::where('state', StateEnum::IN_PROGRESS)
            ->where('end_date', '<', $today)
            ->update(['state' => StateEnum::COMPLETED]);

        // POSTPONED -> IN_PROGRESS (for postponed items that have started but not yet in progress)
        $postponedToInProgress = GuestItem::where('approval', ApprovalEnum::POSTPONED)
            ->where('postponed_at', '<=', $today)
            ->update(['state' => StateEnum::IN_PROGRESS]);

        // IN_PROGRESS -> COMPLETED (for postponed items that have ended but not yet completed)
        $postponedToCompleted = GuestItem::where('approval', ApprovalEnum::POSTPONED)
            ->where('postponed_at', '<', $today)
            ->update(['state' => StateEnum::COMPLETED]);

        // display and log the message in french
        if ($updatedToInProgress > 0 || $updatedToCompleted > 0 || $postponedToInProgress > 0 || $postponedToCompleted > 0) {
            $message = "{$updatedToInProgress} élément(s) mis à jour de En attente à En cours, {$updatedToCompleted} élément(s) mis à jour de En cours à Terminé, {$postponedToInProgress} élément(s) mis à jour de Reporté à En cours, et {$postponedToCompleted} élément(s) mis à jour de Reporté à Terminé.";
            $this->info($message);
            Log::info($message);
        } else {
            $this->info('Aucun élément mis à jour.');
        }
    }
}
