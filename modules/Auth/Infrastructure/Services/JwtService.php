<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Services;

use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Modules\Users\Domain\Entities\User;

/**
 * Serviço para manipulação de tokens JWT
 * Compatível com Swoole/Octane - usa Redis para cache
 */
class JwtService
{
    private const BLACKLIST_PREFIX = 'jwt_blacklist:';
    private const BLACKLIST_TTL = 86400; // 24 horas

    /**
     * Gera um token JWT para o usuário
     */
    public function generateToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    /**
     * Valida e retorna o usuário do token
     */
    public function getUserFromToken(string $token): ?User
    {
        try {
            JWTAuth::setToken($token);
            return JWTAuth::authenticate();
        } catch (JWTException $e) {
            return null;
        }
    }

    /**
     * Refresh do token
     */
    public function refreshToken(): string
    {
        return JWTAuth::refresh();
    }

    /**
     * Invalida o token (logout)
     */
    public function invalidateToken(string $token): bool
    {
        try {
            // Adiciona na blacklist (Redis)
            $key = self::BLACKLIST_PREFIX . $token;
            Cache::put($key, true, self::BLACKLIST_TTL);
            
            JWTAuth::setToken($token);
            JWTAuth::invalidate();
            
            return true;
        } catch (JWTException $e) {
            return false;
        }
    }

    /**
     * Verifica se o token está na blacklist
     */
    public function isTokenBlacklisted(string $token): bool
    {
        $key = self::BLACKLIST_PREFIX . $token;
        return Cache::has($key);
    }

    /**
     * Invalida todos os tokens de um usuário
     * Útil para logout de todos os dispositivos
     */
    public function invalidateAllUserTokens(int $userId): void
    {
        // Implementar lógica de invalidação global
        // TODO: Guardar tokens ativos por usuário no Redis
        $key = "user_tokens:{$userId}";
        Cache::forget($key);
    }
}
