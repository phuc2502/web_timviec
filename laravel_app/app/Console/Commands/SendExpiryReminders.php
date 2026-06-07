<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listings:send-expiry-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi email nhắc nhở nhà tuyển dụng khi tin tuyển dụng còn 3 ngày nữa hết hạn';

    /**
     * Create a new command instance.
     */
    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $threeDaysFromNow = now()->addDays(3)->toDateString();

        $listings = Listing::where('status', 'active')
            ->whereDate('application_close_date', '=', $threeDaysFromNow)
            ->with('user')
            ->get();

        if ($listings->isEmpty()) {
            return;
        }

        $this->info("Đang xử lý nhắc nhở hết hạn cho {$listings->count()} tin...");

        foreach ($listings as $listing) {
            try {
                $this->notificationService->sendExpiryReminderEmail($listing);
                Log::info("Đã gửi email nhắc nhở hết hạn.", ['listing_id' => $listing->id]);
            } catch (\Exception $e) {
                $this->error("Lỗi gửi nhắc nhở cho tin {$listing->id}: " . $e->getMessage());
                Log::error("Lỗi gửi email nhắc nhở hết hạn tin tuyển dụng.", [
                    'listing_id' => $listing->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Hoàn thành gửi email nhắc nhở.');
    }
}
