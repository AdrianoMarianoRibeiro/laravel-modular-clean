<?php

declare(strict_types=1);

namespace Modules\Users\Domain\Repositories;

use Modules\Users\Domain\Entities\User;

/**
 * Interface do repositório de usuários
 * Define o contrato para persistência de usuários
 */
interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function create(array $data): User;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function all(int $perPage = 15): mixed;
    
    public function exists(string $email): bool;
}
