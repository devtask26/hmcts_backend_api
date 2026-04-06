<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;
    
    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->user = $user;
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
    public function test_find_by_id($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        $task = Task::create($dataOne);

        $result = (new TaskRepository())->findById($task->id);

        $this->assertNotEmpty($result);
        $this->assertEquals($task->id, $result->id);
    }

    /**
     * @dataProvider testData
     */
    public function test_all($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        Task::create($dataOne);

        $result = (new TaskRepository())->all();

        $this->assertCount(1, $result);
    }

    /**
     * @dataProvider testData
     */
    public function test_create($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        $result = (new TaskRepository())->create($dataOne);

        $this->assertEquals($dataOne['title'], $result->title);
        $this->assertDatabaseHas('tasks', $dataOne);
    }

    /**
     * @dataProvider testData
     */
    public function test_update($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;
        $task = Task::create($dataOne);
        $updateData = ['title' => 'Updated'];

        $result = (new TaskRepository())->update($task->id, $updateData);

        $this->assertEquals($updateData['title'], $result->title);
        $this->assertDatabaseHas('tasks', $updateData);
    }

    /**
     * @dataProvider testData
     */
    public function test_delete($dataOne): void
    {
        $dataOne['user_id'] = $this->user->id;

        $task = Task::create($dataOne);

        $result = (new TaskRepository())->delete($task->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
