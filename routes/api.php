<?php

use App\Http\Controllers\Api\HomeController;
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

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */

Route::post('login',[HomeController::class, 'login']);
Route::get('users', [HomeController::class, 'index'])->middleware('auth:api');
Route::post('update_weight',[HomeController::class, 'updateWeight']);
Route::post('update_attendance',[HomeController::class, 'updateAttendance']);
Route::get('get_attendance', [HomeController::class, 'getAttendance']);