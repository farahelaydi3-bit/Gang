<?php

namespace App\Services;

use RuntimeException;

/**
 * Transport failure with an HTTP status. No API response bodies or credentials are exposed.
 */
class GangGeminiRequestException extends RuntimeException
{
    public function __construct(
        public readonly int $httpStatus,
        public readonly bool $retryable,
        string $reason = 'Gemini request failed'
    ) {
        parent::__construct($reason, $httpStatus);
    }
}
