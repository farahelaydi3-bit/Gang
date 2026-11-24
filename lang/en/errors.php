<?php

return [
    'home' => 'Back to home',
    'courts' => 'Explore courts',
    'retry' => 'Try again',
    'pages' => [
        401 => ['Sign-in required', 'Sign in to the correct account, then return to this page.'],
        402 => ['Payment required', 'Complete the required payment to continue. If you recently paid, check the status before paying again.'],
        403 => ['Access denied', 'Your account does not have access to this page. Check that you are signed in to the correct account.'],
        404 => ['Page not found', 'This page may have moved or the link may be incorrect. You can return home or explore courts on Gang.'],
        405 => ['Action unavailable', 'This link does not support that action. Return to the relevant page and try from there.'],
        410 => ['This link has expired', 'The offer or page is no longer available. Open Gang again to find the latest options.'],
        413 => ['Upload too large', 'Use a smaller file or upload fewer files at once.'],
        419 => ['Your session expired', 'Open the original page again before retrying your action. Sign in again if prompted.'],
        422 => ['Check your information', 'Return to the form and check the details you entered before trying again.'],
        429 => ['Too many requests', 'Wait a little before trying again.'],
        500 => ['Something went wrong', 'Gang could not load this page. Try again in a moment.'],
        503 => ['Temporarily unavailable', 'Gang is temporarily unavailable. Please check back shortly.'],
        'client' => ['Request could not be completed', 'Check the link or return to the original page and try again.'],
        'server' => ['Something went wrong', 'Gang could not complete your request. Please try again later.'],
    ],
];
