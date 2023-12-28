<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/chat', [App\Http\Controllers\chat\chatController::class, 'chat']);
Route::post('/chat/store', [App\Http\Controllers\chat\chatController::class, 'store']);
Route::get('/chat/messages/{id}', [App\Http\Controllers\chat\chatController::class, 'getUserMessages']);
Route::get('/chat/users', [App\Http\Controllers\chat\chatController::class, 'users']);
Route::get('/chat/userItem/{id}', [App\Http\Controllers\chat\chatController::class, 'userList']);
Route::get('//chat/usersWithHtml', [App\Http\Controllers\chat\chatController::class, 'getUsersWithHtml']);

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
