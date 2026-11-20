<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'Dunki API',
    'status' => 'ok',
    'health' => '/up',
    'api' => '/api',
]));
