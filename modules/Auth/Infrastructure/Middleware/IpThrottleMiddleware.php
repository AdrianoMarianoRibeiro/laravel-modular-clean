<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de proteção contra ataques DDoS/brute force
 * Bloqueia IP por 5 minutos após detectar múltiplas requisições suspeitas
 */
class IpThrottleMiddleware
{
    private const BLOCKED_PREFIX = 'blocked:ip:';
    private const ATTEMPTS_PREFIX = 'attempts:ip:';
    private const MAX_ATTEMPTS = 100; // Máximo de requisições permitidas
    private const WINDOW_SECONDS = 60; // Janela de tempo em segundos
    private const BLOCK_DURATION = 300; // Tempo de bloqueio em segundos (5 minutos)

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $this->getClientIp($request);

        // Verificar se IP está bloqueado
        if ($this->isBlocked($ip)) {
            return response()->json([
                'success' => false,
                'message' => 'Seu IP foi temporariamente bloqueado devido a múltiplas requisições. Tente novamente em alguns minutos.'
            ], 429);
        }

        // Verificar número de tentativas na janela de tempo
        if ($this->exceedsRateLimit($ip)) {
            $this->blockIp($ip);
            
            logger()->warning("IP bloqueado por excesso de requisições", [
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Limite de requisições excedido. Seu IP foi bloqueado por ' . self::BLOCK_DURATION . ' segundos.'
            ], 429);
        }

        // Incrementar contador de tentativas
        $this->incrementAttempts($ip);

        return $next($request);
    }

    /**
     * Obter IP real do cliente (considerando proxies)
     */
    private function getClientIp(Request $request): string
    {
        // Ordem de prioridade para obter IP real
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR'
        ];

        foreach ($ipHeaders as $header) {
            $ip = $request->server($header);
            if ($ip) {
                // Se for lista de IPs (X-Forwarded-For), pegar o primeiro
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }

        return $request->ip();
    }

    /**
     * Verificar se IP está bloqueado
     */
    private function isBlocked(string $ip): bool
    {
        $key = self::BLOCKED_PREFIX . $ip;
        return (bool) Redis::exists($key);
    }

    /**
     * Bloquear IP por tempo determinado
     */
    private function blockIp(string $ip): void
    {
        $key = self::BLOCKED_PREFIX . $ip;
        Redis::setex($key, self::BLOCK_DURATION, '1');
        
        // Limpar contador de tentativas
        Redis::del(self::ATTEMPTS_PREFIX . $ip);
    }

    /**
     * Verificar se excedeu limite de requisições
     */
    private function exceedsRateLimit(string $ip): bool
    {
        $key = self::ATTEMPTS_PREFIX . $ip;
        $attempts = (int) Redis::get($key);
        
        return $attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Incrementar contador de tentativas
     */
    private function incrementAttempts(string $ip): void
    {
        $key = self::ATTEMPTS_PREFIX . $ip;
        
        // Incrementar e definir expiração se for primeira tentativa
        $attempts = Redis::incr($key);
        
        if ($attempts === 1) {
            Redis::expire($key, self::WINDOW_SECONDS);
        }
    }
}
