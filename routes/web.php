<?php

use App\Http\Controllers\LectureController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [LectureController::class, 'index'])->name('dashboard');
    Route::get('/lectures/create', [LectureController::class, 'create'])->name('lectures.create');
    Route::post('/lectures', [LectureController::class, 'store'])->name('lectures.store');
    Route::get('/lectures/{lecture}', [LectureController::class, 'show'])->name('lectures.show');
    Route::get('/lectures/{lecture}/status', [LectureController::class, 'status'])->name('lectures.status');
    Route::post('/lectures/{lecture}/retry', [LectureController::class, 'retry'])->name('lectures.retry');
    Route::delete('/lectures/{lecture}', [LectureController::class, 'destroy'])->name('lectures.destroy');
    Route::get('/lectures/{lecture}/export/{format}', fn () => abort(501))->name('lectures.export');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
