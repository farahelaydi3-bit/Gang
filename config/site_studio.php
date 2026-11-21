<?php

return [
    // Reuses the existing Gang Gemini key. Laravel config:cache is supported.
    'gemini_key' => env('GEMINI_API_KEY'),
    'gemini_model' => env('SITE_STUDIO_GEMINI_MODEL', env('GEMINI_MODEL', 'gemini-2.5-flash')),
    'max_images_per_sport' => 5,
    'trial_months' => 6,
    'monthly_price_jod' => 5,
];
