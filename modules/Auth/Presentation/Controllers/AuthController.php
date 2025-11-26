<?php

declare(strict_types=1);

namespace Modules\Auth\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\Application\DTOs\LoginDTO;
use Modules\Auth\Application\UseCases\AuthenticateUserUseCase;
use Modules\Auth\Infrastructure\Services\JwtService;
use Modules\Auth\Presentation\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;

/**
 * Controlador de autenticação
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticateUserUseCase $authenticateUserUseCase,
        private readonly JwtService $jwtService
    ) {}

    /**
     * Login de usuário
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $dto = LoginDTO::fromArray($request->validated());
            $response = $this->authenticateUserUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => $response->toArray()
            ]);
        } catch (UnauthorizedException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas'
            ], 401);
        } catch (\Exception $e) {
            logger()->error('Erro ao fazer login: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar login'
            ], 500);
        }
    }

    /**
     * Logout do usuário
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();
            
            if ($token) {
                $this->jwtService->revokeToken($token);
            }

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer logout'
            ], 500);
        }
    }

    /**
     * Obter usuário autenticado
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não autenticado'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados do usuário'
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não autenticado'
                ], 401);
            }

            // Revogar token antigo
            $oldToken = $request->bearerToken();
            if ($oldToken) {
                $this->jwtService->revokeToken($oldToken);
            }

            // Gerar novo token
            $newToken = $this->jwtService->generateToken($user);

            return response()->json([
                'success' => true,
                'message' => 'Token renovado com sucesso',
                'data' => [
                    'access_token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => $this->jwtService->getTTL(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao renovar token'
            ], 500);
        }
    }
}
