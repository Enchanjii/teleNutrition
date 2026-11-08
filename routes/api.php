<?php

use App\Http\Controllers\EhrController;
use Illuminate\Support\Facades\Route;

Route::prefix('ehr')->middleware(['throttle:60,1', 'ehr.key'])->group(function () {
    Route::get('/Patient/{id}', [EhrController::class, 'patient'])->name('ehr.patient');
    Route::get('/Observation', [EhrController::class, 'observations'])->name('ehr.observations');
});