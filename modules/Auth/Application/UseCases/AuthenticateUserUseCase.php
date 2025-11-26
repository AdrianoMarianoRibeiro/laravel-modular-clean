<?php

declare(strict_types=1);

namespace Modules\Auth\Application\UseCases;

use Modules\Auth\Application\DTOs\LoginDTO;
use Modules\Auth\Application\DTOs\AuthResponseDTO;
use Modules\Auth\Infrastructure\Services\JwtService;
use Modules\Users\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;

/**
 * Caso de uso: Autenticar usuário
 * Responsável pela lógica de autenticação e geração de token
 */
class AuthenticateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly JwtService $jwtService
    ) {}

    /**
     * Executa autenticação do usuário
     * 
     * @throws UnauthorizedException Se credenciais inválidas
     */
    public function execute(LoginDTO $dto): AuthResponseDTO
    {
        // Buscar usuário por email
        $user = $this->userRepository->findByEmail($dto->email);

        // Verificar se usuário existe e senha está correta
        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw new UnauthorizedException('Credenciais inválidas.');
        }

        // Invalidar tokens anteriores do usuário (opcional - segurança adicional)
        $this->jwtService->revokeUserTokens($user->id);

        // Gerar novo token JWT
        $token = $this->jwtService->generateToken($user);

        return new AuthResponseDTO(
            token: $token,
            type: 'bearer',
            expiresIn: $this->jwtService->getTTL(),
            user: [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        );
    }
}
