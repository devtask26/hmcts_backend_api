<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class TaskControllerTest extends TestCase
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

    /**
     * @dataProvider testData
     */
    public function test_index_returns_all_tasks($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        Task::create($dataOne);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     * @dataProvider testData
     */
    public function test_show_returns_task_when_found($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        $task = Task::create($dataOne);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['id' => $task->id]);
    }

    public function test_show_returns_404_when_task_not_found(): void
    {
        $response = $this->getJson('/api/tasks/99999');

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'success' => false,
                'message' => 'Task not found'
            ]);
    }

    public function test_store_creates_task_with_valid_data(): void
    {
        $taskData = [
            'user_id' => $this->user->id,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'due_date' => now()->addDays(7)->toDateTimeString(),
            'caseNumber' => $this->faker->unique()->numerify('CASE-#####'),
            'status' => 'pending'
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonFragment(['title' => $taskData['title']]);

        $this->assertDatabaseHas('tasks', [
            'title' => $taskData['title'],
            'user_id' => $this->user->id
        ]);
    }

    public function test_store_returns_validation_error_for_missing_required_fields(): void
    {
        $response = $this->postJson('/api/tasks', []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['user_id', 'title', 'description', 'due_date', 'caseNumber', 'status']);
    }

    public function test_store_returns_validation_error_for_invalid_user_id(): void
    {
        $taskData = [
            'user_id' => 99999,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'due_date' => now()->addDays(7)->toDateTimeString(),
            'caseNumber' => $this->faker->unique()->numerify('CASE-#####'),
            'status' => 'pending'
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['user_id']);
    }

    public function test_store_returns_validation_error_for_past_due_date(): void
    {
        $taskData = [
            'user_id' => $this->user->id,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'due_date' => now()->subDays(1)->toDateTimeString(),
            'caseNumber' => $this->faker->unique()->numerify('CASE-#####'),
            'status' => 'pending'
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['due_date']);
    }

    /**
     * @dataProvider testData
     */
    public function test_update_updates_task_with_valid_data($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        $task = Task::create($dataOne);
        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'due_date' => now()->addDays(14)->toDateTimeString(),
            'caseNumber' => $this->faker->unique()->numerify('CASE-#####'),
            'status' => 'completed'
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['title' => 'Updated Title']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title'
        ]);
    }

    public function test_update_returns_404_when_task_not_found(): void
    {
        $updateData = [
            'title' => 'Updated Title',
            'caseNumber' => $this->faker->unique()->numerify('CASE-#####'),
            'status' => 'completed'
        ];

        $response = $this->putJson('/api/tasks/99999', $updateData);

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'success' => false,
                'message' => 'Task not found'
            ]);
    }

    /**
     * @dataProvider testData
     */
    public function test_update_returns_validation_error_for_missing_required_fields($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        $task = Task::create($dataOne);

        $response = $this->putJson("/api/tasks/{$task->id}", []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['caseNumber', 'status']);
    }

    /**
     * @dataProvider testData
     */
    public function test_destroy_deletes_task_when_found($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        $task = Task::create($dataOne);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_destroy_returns_404_when_task_not_found(): void
    {
        $response = $this->deleteJson('/api/tasks/99999');

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'success' => false,
                'message' => 'Task not found'
            ]);
    }

    /**
     * @dataProvider testData
     */
    public function test_trace_id_header_is_present_in_responses($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        Task::create($dataOne);

        $response = $this->getJson('/api/tasks');

        $response->assertHeader('X-Trace-ID');
    }

    /**
     * @dataProvider testData
     */
    public function test_trace_id_header_is_propagated_from_request($dataOne): void
    {
        $traceId = 'test-trace-id-123';
        $dataOne['user_id'] = $this->user->id;
        Task::create($dataOne);

        $response = $this->withHeaders(['X-Trace-ID' => $traceId])
            ->getJson('/api/tasks');

        $response->assertHeader('X-Trace-ID', $traceId);
    }
}