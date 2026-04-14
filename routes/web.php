<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::post('/chat', [ChatController::class, 'store'])->middleware('throttle:chat')->name('chat');
Route::get('/chat/{sessionId}/messages', [ChatController::class, 'messages'])->name('chat.messages');
