<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\ListingStateMachine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listings:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chuyển tin tuyển dụng active hết hạn nộp hồ sơ sang expired';

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
        $listings = Listing::where('status', 'active')
            ->whereDate('application_close_date', '<', now()->toDateString())
            ->get();

        if ($listings->isEmpty()) {
            return;
        }

        $this->info("Đang xử lý hết hạn cho {$listings->count()} tin...");

        foreach ($listings as $listing) {
            try {
                $this->stateMachine->transition($listing, 'expired');
                Log::info("Tin tuyển dụng đã hết hạn.", ['listing_id' => $listing->id]);
            } catch (\Exception $e) {
                $this->error("Lỗi khi xử lý tin tuyển dụng {$listing->id}: " . $e->getMessage());
                Log::error("Lỗi khi chuyển trạng thái tin tuyển dụng sang hết hạn.", [
                    'listing_id' => $listing->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Hoàn thành xử lý tin hết hạn.');
    }
}
