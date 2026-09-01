<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * A business-rule failure that should reach the client as a readable message
 * with a meaningful HTTP status instead of a 500.
 */
class BusinessRuleException extends Exception
{
    /**
     * @param  array<string, mixed>|null  $context
     */
    public function __construct(
        string $message,
        private readonly int $statusCode = 422,
        private readonly ?array $context = null,
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    public function render(Request $request): JsonResponse
    {
        return ApiResponse::error($this->getMessage(), $this->statusCode, [], $this->context);
    }
}
