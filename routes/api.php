<?php

use App\Http\Controllers\InviteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
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

Route::get('accept/{token}', [InviteController::class, 'processInvitation'])->name('process_invitation');
Route::post('register', [InviteController::class, 'register'])->name('user.register');
Route::post('login', [AuthController::class, 'login'])->name('user.login');
Route::post('admin/register', [AuthController::class, 'register'])->name('admin.register');
Route::get('user/{user}', [UserController::class, 'show'])->name('user.show');

Route::group(['middleware' => ['auth:sanctum']], function() {
    Route::resource('invite', InviteController::class);
    Route::resource('user', UserController::class);
    Route::post('admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
});
