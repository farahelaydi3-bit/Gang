<?php

namespace App\Console\Commands;

use App\Models\Court;
use App\Services\CourtReviewSummaryService;
use Illuminate\Console\Command;

class RefreshCourtReviewSummaries extends Command
{
    protected $signature   = 'reviews:refresh-summaries';
    protected $description = 'Generate AI summaries for all courts that have reviews';

    public function handle(CourtReviewSummaryService $service): int
    {
        $courts = Court::whereHas('reviews')->get();
        $bar = $this->output->createProgressBar($courts->count());
        $bar->start();

        foreach ($courts as $court) {
            $service->generate($court);
            $bar->advance();
            sleep(5); // safely under Gemini free-tier 15 rpm
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done: {$courts->count()} courts summarized.");
        return self::SUCCESS;
    }
}