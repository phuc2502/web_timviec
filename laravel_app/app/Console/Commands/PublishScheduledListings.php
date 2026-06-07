<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\ModerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listings:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chuyển tin tuyển dụng lên lịch sang chờ duyệt khi đến thời gian đăng tin';

    /**
     * Create a new command instance.
     */
    public function __construct(
        private ModerationService $moderationService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $listings = Listing::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($listings->isEmpty()) {
            return;
        }

        $this->info("Đang xử lý {$listings->count()} tin tuyển dụng lên lịch...");

        foreach ($listings as $listing) {
            try {
                // Update status to pending_review
                $listing->status = 'pending_review';
                $listing->save();

                // Trigger auto-moderation
                $this->moderationService->autoModerate($listing);

                Log::info("Đăng tin lên lịch thành công.", ['listing_id' => $listing->id]);
            } catch (\Exception $e) {
                $this->error("Lỗi khi đăng tin {$listing->id}: " . $e->getMessage());
                Log::error("Lỗi đăng tin tuyển dụng lên lịch.", [
                    'listing_id' => $listing->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Hoàn thành xử lý tin lên lịch.');
    }
}
