<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rotas web tradicionais (não API).
| Este projeto é principalmente uma API, então as rotas web são mínimas.
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'Laravel Modular Clean API',
        'version' => '1.0.0',
        'documentation' => '/api/documentation',
        'health' => '/api/health',
    ]);
});
