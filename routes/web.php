<?php

use App\Http\Controllers\DailyMissionController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/app-logo', function () {
    return Response::file(resource_path('images/real_world_hero_logo.webp'));
})->name('app.logo');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.submit');
Route::get('/register/parent', [AuthController::class, 'showRegisterParent'])->name('auth.parent.create');
Route::post('/register/parent', [AuthController::class, 'registerParent'])->name('auth.parent.store');
Route::get('/register/child', [AuthController::class, 'showRegisterChild'])->name('auth.child.create');
Route::post('/register/child', [AuthController::class, 'registerChild'])->name('auth.child.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::middleware('auth')->group(function () {
    Route::get('/child', [DailyMissionController::class, 'childDashboard'])->name('mvp.child');
    Route::get('/parent', [DailyMissionController::class, 'parentDashboard'])->name('mvp.parent');
    Route::post('/missions/{mission}/complete', [DailyMissionController::class, 'completeMission'])->name('mvp.complete');
    Route::post('/completions/{completion}/approve', [DailyMissionController::class, 'approveCompletion'])->name('mvp.approve');
});
