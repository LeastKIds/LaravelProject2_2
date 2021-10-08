<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ErrorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['checkEmail'])->post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


Route::prefix('/auth') -> group(function () {
    Route::delete('/leave', [AuthController::class, 'leave']);
    Route::get('/loginCheck', [AuthController::class, 'loginCheck']);
    Route::post('/updatePassword', [AuthController::class, 'updatePassword']);
    Route::post('/emailCheck/', [AuthController::class, 'confirmEmail']);
    Route::post('/reEmailCheck', [AuthController::class, 'reConfirmEmail']);
});





Route::prefix('/error') -> group(function() {
    Route::get('/emailVerifiedFailed',[ErrorController::class, 'emailVerifiedFailed']);
    Route::get('/isNotLogined', [ErrorController::class, 'isNotLogined']);
});

