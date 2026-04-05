<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Services\TaskService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TaskService $taskService,
    ) {}

    public function index(): JsonResponse
    {
        Log::info('Fetching all tasks');
        $tasks = $this->taskService->getAllTasks();

        return $this->success($tasks);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->getTask($id);

            return $this->success($task);
        } catch (ModelNotFoundException) {
            Log::warning('Task not found', ['task_id' => $id]);

            return $this->failure('Task not found', 404);
        }
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());
        Log::info('Task created', ['task_id' => $task->id]);

        return $this->success($task, 'Task created successfully', 201);
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        try {
            $task = $this->taskService->updateTask($id, $request->validated());
            Log::info('Task updated', ['task_id' => $id]);

            return $this->success($task, 'Task updated successfully');
        } catch (ModelNotFoundException) {
            Log::warning('Task not found for update', ['task_id' => $id]);

            return $this->failure('Task not found', 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->taskService->deleteTask($id);
            Log::info('Task deleted', ['task_id' => $id]);

            return $this->success(null, 'Task deleted successfully');
        } catch (ModelNotFoundException) {
            Log::warning('Task not found for deletion', ['task_id' => $id]);

            return $this->failure('Task not found', 404);
        }
    }
}
