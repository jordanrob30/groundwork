<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class AiAnalysisException extends Exception
{
    public function __construct(
        string $message = 'AI analysis failed',
        int $code = 0,
        ?Exception $previous = null,
        public readonly ?array $context = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function rateLimited(): self
    {
        return new self('AI analysis rate limited. Please try again later.', 429);
    }

    public static function invalidResponse(string $details): self
    {
        return new self("Invalid AI response: {$details}", 422);
    }

    public static function apiError(string $message, int $code = 500): self
    {
        return new self("Claude API error: {$message}", $code);
    }
}
