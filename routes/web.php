<?php

use App\Http\Controllers\DailyMissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/child');
});

Route::get('/child', [DailyMissionController::class, 'childDashboard'])->name('mvp.child');
Route::get('/parent', [DailyMissionController::class, 'parentDashboard'])->name('mvp.parent');
Route::post('/missions/{mission}/complete', [DailyMissionController::class, 'completeMission'])->name('mvp.complete');
Route::post('/completions/{completion}/approve', [DailyMissionController::class, 'approveCompletion'])->name('mvp.approve');
