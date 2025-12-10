<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Http\Middleware\CorrelationIdMiddleware;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the CorrelationIdMiddleware.
 */
class CorrelationIdMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a correlation ID is generated for requests without one.
     */
    public function test_generates_correlation_id_when_not_provided(): void
    {
        $response = $this->get('/up');

        $response->assertHeader(CorrelationIdMiddleware::HEADER_NAME);

        $correlationId = $response->headers->get(CorrelationIdMiddleware::HEADER_NAME);
        $this->assertNotNull($correlationId);
        $this->assertTrue(
            preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $correlationId) === 1,
            'Correlation ID should be a valid UUID'
        );
    }

    /**
     * Test that an existing correlation ID from the request header is preserved.
     */
    public function test_preserves_existing_correlation_id(): void
    {
        $existingCorrelationId = '12345678-1234-1234-1234-123456789012';

        $response = $this->withHeader(CorrelationIdMiddleware::HEADER_NAME, $existingCorrelationId)
            ->get('/up');

        $response->assertHeader(CorrelationIdMiddleware::HEADER_NAME, $existingCorrelationId);
    }

    /**
     * Test that the correlation ID is returned in the response header.
     */
    public function test_returns_correlation_id_in_response_header(): void
    {
        $response = $this->get('/up');

        $response->assertHeader(CorrelationIdMiddleware::HEADER_NAME);
    }

    /**
     * Test that different requests get different correlation IDs.
     */
    public function test_generates_unique_ids_for_different_requests(): void
    {
        $response1 = $this->get('/up');
        $response2 = $this->get('/up');

        $correlationId1 = $response1->headers->get(CorrelationIdMiddleware::HEADER_NAME);
        $correlationId2 = $response2->headers->get(CorrelationIdMiddleware::HEADER_NAME);

        $this->assertNotEquals($correlationId1, $correlationId2);
    }
}
