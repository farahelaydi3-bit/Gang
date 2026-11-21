<?php

return [
    // Owner commission is calculated from the full value of each completed booking.
    'commission_percentage' => (float) env('OWNER_COMMISSION_PERCENTAGE', 15),

    // Bump this value whenever the owner agreement changes so stale forms are rejected.
    'terms_version' => env('OWNER_TERMS_VERSION', '2026-11-21-v1'),
];
