<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Persistence;

use Modules\Users\Domain\Entities\User;
use Modules\Users\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Implementação do repositório de usuários usando Eloquent
 */
class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly User $model
    ) {}

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $user = $this->findById($id);
        
        if (!$user) {
            return false;
        }

        return $user->update($data);
    }

    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        
        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function exists(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }
}
