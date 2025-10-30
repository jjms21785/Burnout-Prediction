<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AdminController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes for testing
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/register', function () {
    return view('register');
})->name('register');

// Logout route for testing
Route::post('/logout', function () {
    return redirect()->route('login')->with('message', 'Logged out successfully');
})->name('logout');

Route::get('/assessment', [AssessmentController::class, 'index'])->name('assessment.index');
Route::post('/assessment', [AssessmentController::class, 'store'])->name('assessment.store');
Route::get('/results/{id}', [AssessmentController::class, 'results'])->name('assessment.results');
Route::post('/assessment/result', [AssessmentController::class, 'calculateBurnout'])->name('assessment.result');

// Admin routes with clean URLs
Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/view', [AdminController::class, 'report'])->name('admin.report');
Route::get('/view/programs', [AdminController::class, 'reportPrograms'])->name('admin.report.programs');
Route::get('/questions', [AdminController::class, 'questions'])->name('admin.questions');
Route::post('/questions/update', [AdminController::class, 'updateQuestions'])->name('admin.questions.update');
Route::get('/files', [AdminController::class, 'files'])->name('admin.files');
Route::post('/files/import', [AdminController::class, 'importData'])->name('admin.import-data');
Route::get('/files/export', [AdminController::class, 'exportData'])->name('admin.export-data');
Route::get('/files/download/{filename}', [AdminController::class, 'downloadFile'])->name('admin.download-file');
Route::post('/files/delete/{filename}', [AdminController::class, 'deleteFile'])->name('admin.delete-file');
Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
Route::post('/settings/update', [AdminController::class, 'updateSettings'])->name('admin.settings.update');