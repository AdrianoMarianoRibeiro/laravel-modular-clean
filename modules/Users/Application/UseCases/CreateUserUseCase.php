<?php

declare(strict_types=1);

namespace Modules\Users\Application\UseCases;

use Modules\Users\Application\DTOs\CreateUserDTO;
use Modules\Users\Domain\Repositories\UserRepositoryInterface;
use Modules\Users\Domain\Entities\User;
use Illuminate\Support\Facades\Hash;

/**
 * Use Case: Criar novo usuário
 * 
 * Responsabilidade: Orquestrar a criação de um novo usuário no sistema
 */
class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Executa a criação do usuário
     *
     * @throws \Exception Se email já existe
     */
    public function execute(CreateUserDTO $dto): User
    {
        // Verificar se email já existe
        if ($this->userRepository->existsByEmail($dto->email)) {
            throw new \Exception('Email já cadastrado no sistema');
        }

        // Criar usuário
        $user = new User();
        $user->name = $dto->name;
        $user->email = $dto->email;
        $user->password = $dto->password; // Será hashado pelo mutator do model

        return $this->userRepository->create($user);
    }
}
