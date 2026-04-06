<?php
 
namespace Tests\Unit\Services;
 
use App\Models\Task;
use App\Repositories\TaskRepositoryInterface;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;
 
class TaskServiceTest extends TestCase
{
    private TaskRepositoryInterface $mockRepository;
    private TaskService $taskService;
 
    public function setUp(): void
    {
        $this->mockRepository = $this->getMockBuilder(TaskRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taskService = new TaskService($this->mockRepository);
    }
 
    public function test_get_all_tasks(): void
    {
        $expectedTasks = new Collection([
            new Task(['id' => 1, 'title' => 'Task 1']),
            new Task(['id' => 2, 'title' => 'Task 2']),
        ]);
 
        $this->mockRepository
            ->method('all')
            ->willReturn($expectedTasks);
 
        $result = $this->taskService->getAllTasks();
 
        $this->assertEquals($expectedTasks, $result);
    }
 
    public function test_get_task(): void
    {
        $taskId = 1;
        $expectedTask = new Task(['id' => $taskId, 'title' => 'Test Task']);
 
        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with($taskId)
            ->willReturn($expectedTask);
 
        $result = $this->taskService->getTask($taskId);
 
        $this->assertEquals($expectedTask, $result);
    }
 
    public function test_create_task(): void
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'user_id' => 1,
        ];
        $expectedTask = new Task($taskData);
 
        $this->mockRepository
            ->expects($this->once())
            ->method('create')
            ->with($taskData)
            ->willReturn($expectedTask);
 
        $result = $this->taskService->createTask($taskData);
 
        $this->assertEquals($expectedTask, $result);
    }
 
    public function test_update_task(): void
    {
        $taskId = 1;
        $updateData = ['title' => 'Updated Task'];
        $expectedTask = new Task(['id' => $taskId, ...$updateData]);
 
        $this->mockRepository
            ->expects($this->once())
            ->method('update')
            ->with($taskId, $updateData)
            ->willReturn($expectedTask);
 
        $result = $this->taskService->updateTask($taskId, $updateData);
 
        $this->assertEquals($expectedTask, $result);
    }
 
    public function test_delete_task(): void
    {
        $taskId = 1;
 
        $this->mockRepository
            ->expects($this->once())
            ->method('delete')
            ->with($taskId)
            ->willReturn(true);
 
        $result = $this->taskService->deleteTask($taskId);
 
        $this->assertTrue($result);
    }
 
    public function test_delete_task_returns_false_on_failure(): void
    {
        $taskId = 999;
 
        $this->mockRepository
            ->expects($this->once())
            ->method('delete')
            ->with($taskId)
            ->willReturn(false);
 
        $result = $this->taskService->deleteTask($taskId);
 
        $this->assertFalse($result);
    }
}
