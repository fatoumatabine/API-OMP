<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'OMPAY API is running successfully',
        'status' => 'OK',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
        'documentation' => url('/api/documentation')
    ]);
});

// Swagger/OpenAPI routes
Route::get('/api/documentation', function () {
    return view('l5-swagger::index');
});

Route::get('/api-docs.json', function () {
    return response()->file(storage_path('api-docs/api-docs.json'));
});
