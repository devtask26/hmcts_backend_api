<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): Task;
    public function create(array $data): Task;
    public function update(int $id, array $data): Task;
    public function delete(int $id): bool;
}