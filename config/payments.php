<?php

return [
    'gateway' => env('PAYMENT_GATEWAY', env('APP_ENV', 'production') === 'local' ? 'demo' : 'stripe'),

    'demo_enabled' => env('PAYMENT_DEMO_ENABLED', env('APP_ENV', 'production') === 'local'),

    'currency' => env('PAYMENT_CURRENCY', 'usd'),
];