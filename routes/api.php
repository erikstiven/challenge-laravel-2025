<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\OrderController;

Route::post('debug/echo', function (Request $request) {
    return response()->json([
        'content_type' => $request->header('Content-Type'),
        'accept'       => $request->header('Accept'),
        'json'         => $request->all(),
    ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
});

Route::get('debug/cache', function () {
    return response()->json([
        'driver'        => app('cache')->getDefaultDriver(),
        'store_default' => config('cache.default'),
        'redis_client'  => config('database.redis.client') ?? config('redis.client'),
        'store_class'   => get_class(Cache::getStore()),
    ]);
});

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show'])->whereUuid('id');
Route::post('/orders/{id}/advance', [OrderController::class, 'advance'])->whereUuid('id');


