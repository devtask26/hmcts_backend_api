<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function all(): Collection
    {
        return Task::all();
    }

    public function findById(int $id): Task
    {
        return Task::findOrFail($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(int $id, array $data): Task
    {
        $task = Task::findOrFail($id);
        $task->update($data);

        return $task->fresh();
    }

    public function delete(int $id): bool
    {
        $task = Task::findOrFail($id);

        return (bool) $task->delete();
    }
}
