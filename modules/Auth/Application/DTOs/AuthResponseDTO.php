<?php

declare(strict_types=1);

namespace Modules\Auth\Application\DTOs;

/**
 * DTO de resposta da autenticação
 */
readonly class AuthResponseDTO
{
    public function __construct(
        public string $token,
        public string $type,
        public int $expiresIn,
        public array $user
    ) {}

    public function toArray(): array
    {
        return [
            'access_token' => $this->token,
            'token_type' => $this->type,
            'expires_in' => $this->expiresIn,
            'user' => $this->user,
        ];
    }
}
