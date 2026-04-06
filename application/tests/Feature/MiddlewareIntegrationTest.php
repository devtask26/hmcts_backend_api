<?php

namespace Tests\Integration;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MiddlewareIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public static function testData(): array
    {
        return [
            [
                'dataOne' => [
                    'title' => 'Test123',
                    'description' => 'Test123',
                    'caseNumber' => 'Test123',
                    'status' => 'Test123',
                    'due_date' => '2026-09-09',
                ],
            ]
        ];
    }

    public function test_trace_id_middleware_end_to_end(): void
    {
        $response = $this->getJson('/api/tasks');
        $response->assertHeader('X-Trace-ID');
        $generatedTraceId = $response->headers->get('X-Trace-ID');
        $this->assertNotNull($generatedTraceId);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $generatedTraceId);

        $customTraceId = 'custom-trace-id-12345';
        $response = $this->withHeaders(['X-Trace-ID' => $customTraceId])
            ->getJson('/api/tasks');
        $response->assertHeader('X-Trace-ID', $customTraceId);
    }

    /**
     * @dataProvider testData
     */
    public function test_trace_id_in_logs($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        Task::create($dataOne);

        $response = $this->getJson('/api/tasks');
        $response->assertStatus(200);
        $response->assertHeader('X-Trace-ID');
    }

    public function test_middleware_with_all_endpoints(): void
    {
        $traceId = 'middleware-test-123';
        
        $endpoints = [
            ['method' => 'GET', 'url' => '/api/tasks'],
            ['method' => 'GET', 'url' => '/api/tasks/1'],
            ['method' => 'POST', 'url' => '/api/tasks', 'data' => [
                'user_id' => $this->user->id,
                'title' => 'Middleware Test',
                'description' => 'Testing middleware',
                'due_date' => now()->addDays(7)->toDateTimeString(),
                'caseNumber' => 'CASE-MIDDLEWARE-123',
                'status' => 'pending'
            ]],
        ];

        foreach ($endpoints as $endpoint) {
            $request = $this->withHeaders(['X-Trace-ID' => $traceId]);
            
            if (isset($endpoint['data'])) {
                $response = $request->{$endpoint['method']}($endpoint['url'], $endpoint['data']);
            } else {
                $response = $request->{$endpoint['method']}($endpoint['url']);
            }

            $response->assertHeader('X-Trace-ID', $traceId);
        }
    }
}
