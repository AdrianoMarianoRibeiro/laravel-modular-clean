<?php

declare(strict_types=1);

namespace Modules\Users\Application\UseCases;

use Modules\Users\Domain\Entities\User;
use Modules\Users\Domain\Repositories\UserRepositoryInterface;

/**
 * Caso de uso: Buscar usuário por ID
 */
class GetUserByIdUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Busca um usuário pelo ID
     *
     * @param int $id
     * @return User|null
     */
    public function execute(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }
}
