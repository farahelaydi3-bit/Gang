<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\StripeRefundService;
use Illuminate\Console\Command;

class ReconcileBookingRefunds extends Command
{
    protected $signature = 'gang:reconcile-refunds {--payment= : Only process this local payment ID}';
    protected $description = 'Retry requested Stripe booking refunds and refresh pending refunds';

    public function handle(StripeRefundService $service): int
    {
        $query = Payment::query()->whereIn('refund_status', ['requested', 'pending']);
        if ($this->option('payment') !== null) {
            $query->whereKey($this->option('payment'));
        }
        $query->chunkById(50, function ($payments) use ($service) {
            foreach ($payments as $payment) {
                $service->process($payment->id);
            }
        });
        $this->info('Refund reconciliation completed. Check refund_status for the actual outcome.');
        return self::SUCCESS;
    }
}
