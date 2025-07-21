<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');

Route::get('/assessment', [AssessmentController::class, 'index'])->name('assessment.index');
Route::post('/assessment', [AssessmentController::class, 'store'])->name('assessment.store');
Route::get('/results/{id}', [AssessmentController::class, 'results'])->name('assessment.results');

Route::prefix('admin')->group(function () {
    Route::get('/students', [AdminController::class, 'students'])->name('admin.students');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
});