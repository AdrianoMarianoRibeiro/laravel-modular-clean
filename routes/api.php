<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rotas da API RESTful.
| Todas as rotas aqui automaticamente têm prefixo /api
|
*/

// Health check - rota pública para monitoramento
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'Laravel Modular Clean'
    ]);
});

// =============================================================================
// MÓDULO: AUTH - Autenticação JWT
// =============================================================================
Route::prefix('auth')->group(function () {
    // Rotas públicas
    Route::post('/register', [\Modules\Auth\Presentation\Controllers\AuthController::class, 'register']);
    Route::post('/login', [\Modules\Auth\Presentation\Controllers\AuthController::class, 'login']);
    
    // Rotas protegidas (requerem autenticação JWT)
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [\Modules\Auth\Presentation\Controllers\AuthController::class, 'logout']);
        Route::post('/refresh', [\Modules\Auth\Presentation\Controllers\AuthController::class, 'refresh']);
        Route::get('/me', [\Modules\Auth\Presentation\Controllers\AuthController::class, 'me']);
    });
});

// =============================================================================
// MÓDULO: USERS - Gestão de Usuários
// =============================================================================
Route::prefix('users')->middleware('auth:api')->group(function () {
    Route::get('/', [\Modules\Users\Presentation\Controllers\UserController::class, 'index']);
    Route::get('/{id}', [\Modules\Users\Presentation\Controllers\UserController::class, 'show']);
    Route::post('/', [\Modules\Users\Presentation\Controllers\UserController::class, 'store']);
    Route::put('/{id}', [\Modules\Users\Presentation\Controllers\UserController::class, 'update']);
    Route::delete('/{id}', [\Modules\Users\Presentation\Controllers\UserController::class, 'destroy']);
});

// =============================================================================
// MÓDULO: DOCS - Manipulação de Documentos
// =============================================================================
// Route::prefix('docs')->middleware(['auth:api', 'throttle:60,1'])->group(function () {
    
//     // Conversão de formatos
//     Route::post('/convert/image-to-pdf', [\Modules\Docs\Controllers\DocumentController::class, 'convertImageToPdf']);
//     Route::post('/convert/doc-to-pdf', [\Modules\Docs\Controllers\DocumentController::class, 'convertDocToPdf']);
//     Route::post('/convert/pdf-to-images', [\Modules\Docs\Controllers\DocumentController::class, 'convertPdfToImages']);
    
//     // Extração de dados
//     Route::post('/extract/text', [\Modules\Docs\Controllers\DocumentController::class, 'extractText']);
    
//     // Manipulação de PDFs
//     Route::post('/merge', [\Modules\Docs\Controllers\DocumentController::class, 'mergePdfs']);
//     Route::post('/split', [\Modules\Docs\Controllers\DocumentController::class, 'splitPdf']);
    
//     // Hash e assinatura
//     Route::post('/hash-pages', [\Modules\Docs\Controllers\DocumentController::class, 'generatePageHashes']);
//     Route::post('/sign', [\Modules\Docs\Controllers\DocumentController::class, 'signDocument']);
    
//     // Listagem e consulta
//     Route::get('/', [\Modules\Docs\Controllers\DocumentController::class, 'index']);
//     Route::get('/{id}', [\Modules\Docs\Controllers\DocumentController::class, 'show']);
//     Route::delete('/{id}', [\Modules\Docs\Controllers\DocumentController::class, 'destroy']);
// });

// // =============================================================================
// // MÓDULO: WORKERS - Status de Jobs/Filas (administrativo)
// // =============================================================================
// Route::prefix('workers')->middleware(['auth:api', 'admin'])->group(function () {
//     Route::get('/status', [\Modules\Workers\Controllers\WorkerController::class, 'status']);
//     Route::get('/jobs', [\Modules\Workers\Controllers\WorkerController::class, 'listJobs']);
//     Route::post('/jobs/{id}/retry', [\Modules\Workers\Controllers\WorkerController::class, 'retryJob']);
//     Route::delete('/jobs/{id}', [\Modules\Workers\Controllers\WorkerController::class, 'deleteJob']);
// });

// =============================================================================
// ROTAS DE TESTE/DEBUG (remover em produção)
// =============================================================================
if (config('app.debug')) {
    Route::prefix('debug')->group(function () {
        // Testar conexões
        Route::get('/redis', function () {
            try {
                $redis = app('redis')->connection();
                $redis->ping();
                return response()->json(['status' => 'Redis OK']);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
        
        Route::get('/database', function () {
            try {
                \DB::connection()->getPdo();
                return response()->json(['status' => 'Database OK']);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
        
        Route::get('/rabbitmq', function () {
            try {
                // TODO: Implementar teste de conexão RabbitMQ
                return response()->json(['status' => 'RabbitMQ test not implemented']);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    });
}
