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
