<?php
return [
    'history' => 'Recent cancellations',
    'status' => 'Refund status',
    'other_booking' => 'The existing cancellation rules apply to this booking type.',
    'outside_window' => 'The 24-hour refund window has ended. You may cancel before play starts, but the payment will not be refunded.',
    'eligible_paid' => 'Within the 24-hour window. Recorded payment: :amount (booking amount). Eligible card payments will be refunded in their original Stripe currency after verification.',
    'verify_checkout' => 'Your checkout will be checked before cancellation. If payment was completed, the eligible amount will be refunded.',
    'unpaid' => 'No successful payment is recorded. If none was made, no refund is needed.',
    'cancelled_message' => 'Booking cancelled. The time is available again. Check recent cancellations for refund status.',
    'no_record' => 'No refund recorded for this booking.',
    'demo' => 'simulation only',
    'states' => [
        'requested' => 'Requested — awaiting processing', 'pending' => 'Pending with Stripe',
        'succeeded' => 'Refund completed by Stripe', 'failed' => 'Refund failed — contact support',
        'manual_review' => 'Manual review required — contact support', 'not_eligible' => 'Not eligible (24-hour window ended)',
        'demo_refunded' => 'Simulated refund — no real money moved',
    ],
];
