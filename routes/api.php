<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/get-info', [App\Http\Controllers\BoardController::class, 'index']);
Route::post('/create-column', [App\Http\Controllers\BoardController::class, 'storeColumn']);
Route::post('/create-card', [App\Http\Controllers\BoardController::class, 'storeCard']);
Route::get('/get-card-info/{id}', [App\Http\Controllers\BoardController::class, 'getCardInfo']);
Route::post('/update-card', [App\Http\Controllers\BoardController::class, 'updateCard']);
Route::post('/relocate-card', [App\Http\Controllers\BoardController::class, 'moveCard']);
Route::delete('/delete-column/{id}', [App\Http\Controllers\BoardController::class, 'deleteColumn']);

Route::get('/export-db', [App\Http\Controllers\BoardController::class, 'exportDB']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
