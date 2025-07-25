<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AdminController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');    

Route::get('/assessment', [AssessmentController::class, 'index'])->name('assessment.index');
Route::post('/assessment', [AssessmentController::class, 'store'])->name('assessment.store');
Route::get('/results/{id}', [AssessmentController::class, 'results'])->name('assessment.results');
Route::post('/assessment/result', [AssessmentController::class, 'calculateBurnout'])->name('assessment.result');

Route::prefix('admin')->group(function () {
    Route::get('/students', [AdminController::class, 'students'])->name('admin.students');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/data-monitoring', [AdminController::class, 'dataMonitoring'])->name('admin.data-monitoring');
    Route::get('/data-monitoring/programs', [AdminController::class, 'dataMonitoringPrograms'])->name('admin.data-monitoring.programs');
    Route::get('/high-risk-students', [AdminController::class, 'topHighRiskStudents'])->name('admin.high-risk-students');
    Route::post('/import', [AdminController::class, 'import'])->name('admin.import');
});