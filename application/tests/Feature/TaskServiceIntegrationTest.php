<?php

namespace Tests\Integration;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskServiceIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private TaskService $taskService;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->taskService = new TaskService(new TaskRepository());
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

    public function test_service_with_real_database_operations(): void
    {
        // Create task through service
        $taskData = [
            'user_id' => $this->user->id,
            'title' => 'Service Integration Test',
            'description' => 'Testing service with real DB',
            'due_date' => now()->addDays(7)->toDateTimeString(),
            'caseNumber' => 'CASE-SERVICE-123',
            'status' => 'pending'
        ];

        $task = $this->taskService->createTask($taskData);
        
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($taskData['title'], $task->title);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);

        // Read task through service
        $retrievedTask = $this->taskService->getTask($task->id);
        $this->assertEquals($task->id, $retrievedTask->id);

        // Update task through service
        $updateData = ['title' => 'Updated Service Task'];
        $updatedTask = $this->taskService->updateTask($task->id, $updateData);
        $this->assertEquals('Updated Service Task', $updatedTask->title);

        // Delete task through service
        $deleteResult = $this->taskService->deleteTask($task->id);
        $this->assertTrue($deleteResult);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * @dataProvider testData
     */
    public function test_service_get_all_tasks_with_database($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        Task::create($dataOne);

        $allTasks = $this->taskService->getAllTasks();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $allTasks);
    }

    public function test_service_error_handling_with_database(): void
    {
        // Test non-existent task
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->taskService->getTask(99999);

        // Test update non-existent task
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->taskService->updateTask(99999, ['title' => 'Should not work']);

        // Test delete non-existent task
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->taskService->deleteTask(99999);
    }
}
