<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Support\Collection;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
    ) {}

    public function getAllTasks(): Collection
    {
        return $this->tasks->all();
    }

    public function getTask(int $id): Task
    {
        return $this->tasks->findById($id);
    }

    public function createTask(array $data): Task
    {
        return $this->tasks->create($data);
    }

    public function updateTask(int $id, array $data): Task
    {
        return $this->tasks->update($id, $data);
    }

    public function deleteTask($id): bool
    {
        return $this->tasks->delete($id);
    }
}