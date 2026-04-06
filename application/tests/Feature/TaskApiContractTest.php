<?php

namespace Tests\Integration;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskApiContractTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_api_response_structure_consistency(): void
    {
        $taskData = [
            'user_id' => $this->user->id,
            'title' => 'Contract Test',
            'description' => 'Testing API contract',
            'due_date' => now()->addDays(7)->toDateTimeString(),
            'caseNumber' => 'CASE-CONTRACT-123',
            'status' => 'pending'
        ];

        $createResponse = $this->postJson('/api/tasks', $taskData);
        $createResponse->assertStatus(201);
        $this->assertIsArray($createResponse->json());

        $this->getJson('/api/tasks')
            ->assertStatus(200)
            ->assertJsonIsArray();

        $taskId = $createResponse->json('id');
        $this->getJson("/api/tasks/{$taskId}")
            ->assertStatus(200);

        $this->putJson("/api/tasks/{$taskId}", [
            'caseNumber' => 'CASE-CONTRACT-456',
            'status' => 'completed'
        ])
            ->assertStatus(200);
    }

    public function test_error_response_structure_consistency(): void
    {
        $this->getJson('/api/tasks/99999')
            ->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message'
            ]);

        $this->postJson('/api/tasks', [])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);
    }

    public function test_required_headers_present(): void
    {
        $this->getJson('/api/tasks')
            ->assertHeader('X-Trace-ID');
        
        $this->postJson('/api/tasks', [
            'user_id' => $this->user->id,
            'title' => 'Header Test',
            'description' => 'Testing headers',
            'due_date' => now()->addDays(7)->toDateTimeString(),
            'caseNumber' => 'CASE-HEADER-123',
            'status' => 'pending'
        ])
            ->assertHeader('X-Trace-ID');
    }
}
