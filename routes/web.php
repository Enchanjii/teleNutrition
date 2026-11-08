<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DeidentifiedExportController;
use App\Http\Controllers\DietitianAppointmentController;
use App\Http\Controllers\DietitianDashboardController;
use App\Http\Controllers\MealLogController;
use App\Http\Controllers\PatientChartController;
use App\Http\Controllers\PatientDashboardController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VideoSessionController;
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
    Route::get('/patient/dashboard', PatientDashboardController::class)->name('patient.dashboard');
    Route::get('/patient/meals', [MealLogController::class, 'index'])->name('patient.meals.index');
    Route::post('/patient/meals', [MealLogController::class, 'store'])->name('patient.meals.store');
    Route::get('/patient/profile', [PatientProfileController::class, 'edit'])->name('patient.profile.edit');
    Route::put('/patient/profile', [PatientProfileController::class, 'update'])->name('patient.profile.update');
    Route::get('/patient/appointments', [AppointmentController::class, 'index'])->name('patient.appointments.index');
    Route::post('/patient/appointments', [AppointmentController::class, 'store'])->name('patient.appointments.store');
    Route::patch('/patient/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('patient.appointments.cancel');
});

Route::middleware(['auth', 'role:dietitian'])->group(function () {
    Route::get('/dietitian/dashboard', DietitianDashboardController::class)->name('dietitian.dashboard');
    Route::get('/dietitian/patients/{patient}', [PatientChartController::class, 'show'])
        ->middleware('audit:chart.view')->name('dietitian.patients.show');
    Route::post('/dietitian/patients/{patient}/notes', [PatientChartController::class, 'store'])
        ->middleware('audit:chart.note.create')->name('dietitian.patients.notes.store');
    Route::get('/dietitian/export', DeidentifiedExportController::class)
        ->middleware('audit:export.deidentified')->name('dietitian.export');
    Route::get('/dietitian/appointments', [DietitianAppointmentController::class, 'index'])->name('dietitian.appointments.index');
    Route::patch('/dietitian/appointments/{appointment}', [DietitianAppointmentController::class, 'update'])->name('dietitian.appointments.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/appointments/{appointment}/session', VideoSessionController::class)->name('appointments.session');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';