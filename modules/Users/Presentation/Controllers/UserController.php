<?php

declare(strict_types=1);

namespace Modules\Users\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Modules\Users\Application\DTOs\CreateUserDTO;
use Modules\Users\Application\UseCases\CreateUserUseCase;
use Modules\Users\Application\UseCases\GetUserByIdUseCase;
use Modules\Users\Presentation\Requests\CreateUserRequest;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

/**
 * Controlador de usuários
 * Camada de apresentação - recebe requests e retorna responses
 */
class UserController extends Controller
{
    public function __construct(
        private readonly CreateUserUseCase $createUserUseCase,
        private readonly GetUserByIdUseCase $getUserByIdUseCase
    ) {}

    /**
     * Criar novo usuário
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $dto = CreateUserDTO::fromArray($request->validated());
            $user = $this->createUserUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'data' => $user->toArray()
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar usuário'
            ], 500);
        }
    }

    /**
     * Buscar usuário por ID
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->getUserByIdUseCase->execute($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user->toArray()
        ]);
    }
}
