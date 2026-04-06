<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Services\TaskService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    use ApiResponse, ValidatesRequests;

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

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'caseNumber' => 'required|string',
            'status' => 'required|string',
        ]);
 
        if ($validator->fails()) {
            return $this->failure(
                'Unprocessable entity',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $validator->errors()
            );
        }

        $task = $this->taskService->createTask($request->validated());
        Log::info('Task created', ['task_id' => $task->id]);

        return $this->success($task, 'Task created successfully', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
                'description' => 'string',
                'due_date' => 'date|after:now',
                'caseNumber' => 'required|string',
                'status' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                return $this->failure(
                    'Unprocessable entity',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $validator->errors()
                );
            }

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
