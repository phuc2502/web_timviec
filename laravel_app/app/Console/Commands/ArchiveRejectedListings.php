<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\ListingStateMachine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ArchiveRejectedListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listings:archive-rejected';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lưu trữ các tin tuyển dụng đã bị từ chối trên 30 ngày';

    /**
     * Create a new command instance.
     */
    public function __construct(
        private ListingStateMachine $stateMachine
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $thirtyDaysAgo = now()->subDays(30);

        $listings = Listing::where('status', 'rejected')
            ->where('rejected_at', '<=', $thirtyDaysAgo)
            ->get();

        if ($listings->isEmpty()) {
            return;
        }

        $this->info("Đang xử lý lưu trữ {$listings->count()} tin bị từ chối...");

        foreach ($listings as $listing) {
            try {
                // Transition to archived
                $this->stateMachine->transition($listing, 'archived', 'Tự động lưu trữ sau 30 ngày từ chối.');
                
                $listing->archived_reason = 'auto_expired';
                $listing->save();

                Log::info("Tin tuyển dụng bị từ chối đã được lưu trữ.", ['listing_id' => $listing->id]);
            } catch (\Exception $e) {
                $this->error("Lỗi khi lưu trữ tin {$listing->id}: " . $e->getMessage());
                Log::error("Lỗi khi tự động lưu trữ tin tuyển dụng bị từ chối.", [
                    'listing_id' => $listing->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Hoàn thành lưu trữ tin bị từ chối.');
    }
}
