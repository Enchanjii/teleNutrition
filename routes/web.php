<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return auth()->user()->role === 'dietitian'
        ? redirect()->route('dietitian.dashboard')
        : redirect()->route('patient.dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'role:patient'])->group(function () {
    Route::view('/patient/dashboard', 'patient.dashboard')->name('patient.dashboard');
});

Route::middleware(['auth', 'role:dietitian'])->group(function () {
    Route::view('/dietitian/dashboard', 'dietitian.dashboard')->name('dietitian.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';