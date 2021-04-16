<?php

use App\Http\Controllers\InviteController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('invite', InviteController::class);
Route::get('accept/{token}', [InviteController::class, 'processInvitation'])->name('process_invitation');
Route::post('register', [InviteController::class, 'register'])->name('register');
