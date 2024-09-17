<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

Route::get('/', [BookingController::class, 'index'])->name('home');
Route::post('/book', [BookingController::class, 'book'])->name('book');
Route::post('/create-new-table', [BookingController::class, 'createNewTable'])->name('create.new.table');
