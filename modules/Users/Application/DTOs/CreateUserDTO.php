<?php

declare(strict_types=1);

namespace Modules\Users\Application\DTOs;

/**
 * DTO para criação de usuário
 */
readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}

    /**
     * Cria instância a partir de array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password']
        );
    }
}
