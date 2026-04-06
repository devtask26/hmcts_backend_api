<?php

namespace Tests\Integration;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskWorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_complete_task_lifecycle(): void
    {
        // 1. Create task
        $createData = [
            'user_id' => $this->user->id,
            'title' => 'Initial Task',
            'description' => 'Initial Description',
            'due_date' => now()->addDays(7)->toDateTimeString(),
            'caseNumber' => 'CASE-12345',
            'status' => 'pending'
        ];

        $createResponse = $this->postJson('/api/tasks', $createData);
        $createResponse->assertStatus(201);
        
        $taskId = $createResponse->json('id');
        $this->assertNotNull($taskId);

        // 2. Read task - verify it exists
        $readResponse = $this->getJson("/api/tasks/{$taskId}");
        $readResponse->assertStatus(200)
            ->assertJsonFragment(['title' => 'Initial Task']);

        // 3. Update task
        $updateData = [
            'title' => 'Updated Task Title',
            'description' => 'Updated Description',
            'due_date' => now()->addDays(14)->toDateTimeString(),
            'caseNumber' => 'CASE-67890',
            'status' => 'in_progress'
        ];

        $updateResponse = $this->putJson("/api/tasks/{$taskId}", $updateData);
        $updateResponse->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Task Title']);

        // 4. Verify update persisted
        $verifyResponse = $this->getJson("/api/tasks/{$taskId}");
        $verifyResponse->assertStatus(200)
            ->assertJsonFragment(['status' => 'in_progress']);

        // 5. Delete task
        $deleteResponse = $this->deleteJson("/api/tasks/{$taskId}");
        $deleteResponse->assertStatus(200);

        // 6. Verify deletion
        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
        $finalResponse = $this->getJson("/api/tasks/{$taskId}");
        $finalResponse->assertStatus(404);
    }

    public function test_multiple_tasks_workflow(): void
    {
        // Create multiple tasks
        $taskIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $taskData = [
                'user_id' => $this->user->id,
                'title' => "Task {$i}",
                'description' => "Description {$i}",
                'due_date' => now()->addDays($i)->toDateTimeString(),
                'caseNumber' => "CASE-{$i}0000",
                'status' => 'pending'
            ];

            $response = $this->postJson('/api/tasks', $taskData);
            $response->assertStatus(201);
            $taskIds[] = $response->json('id');
        }

        // Verify all tasks exist in index
        $indexResponse = $this->getJson('/api/tasks');
        $indexResponse->assertStatus(200)
            ->assertJsonCount(3);

        // Update all tasks to different statuses
        $statuses = ['in_progress', 'completed', 'cancelled'];
        foreach ($taskIds as $index => $taskId) {
            $updateData = [
                'caseNumber' => "CASE-UPDATED-{$taskId}",
                'status' => $statuses[$index]
            ];

            $this->putJson("/api/tasks/{$taskId}", $updateData)
                ->assertStatus(200);
        }

        // Verify all updates persisted
        foreach ($taskIds as $index => $taskId) {
            $response = $this->getJson("/api/tasks/{$taskId}");
            $response->assertStatus(200)
                ->assertJsonFragment(['status' => $statuses[$index]]);
        }
    }

    public function test_task_workflow_with_trace_id_consistency(): void
    {
        $traceId = 'integration-test-trace-id';
        
        $taskData = [
            'user_id' => $this->user->id,
            'title' => 'Trace Test Task',
            'description' => 'Testing trace ID flow',
            'due_date' => now()->addDays(7)->toDateTimeString(),
            'caseNumber' => 'CASE-TRACE-123',
            'status' => 'pending'
        ];

        // Create with trace ID
        $createResponse = $this->withHeaders(['X-Trace-ID' => $traceId])
            ->postJson('/api/tasks', $taskData);
        
        $createResponse->assertStatus(201)
            ->assertHeader('X-Trace-ID', $traceId);

        $taskId = $createResponse->json('id');

        // All subsequent requests should maintain trace ID
        $this->withHeaders(['X-Trace-ID' => $traceId])
            ->getJson("/api/tasks/{$taskId}")
            ->assertHeader('X-Trace-ID', $traceId);

        $this->withHeaders(['X-Trace-ID' => $traceId])
            ->putJson("/api/tasks/{$taskId}", [
                'caseNumber' => 'CASE-TRACE-456',
                'status' => 'completed'
            ])
            ->assertHeader('X-Trace-ID', $traceId);

        $this->withHeaders(['X-Trace-ID' => $traceId])
            ->deleteJson("/api/tasks/{$taskId}")
            ->assertHeader('X-Trace-ID', $traceId);
    }
}
