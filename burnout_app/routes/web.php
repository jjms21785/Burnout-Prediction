<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\FileController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/auth/check', [AuthController::class, 'checkAuth'])->name('auth.check');

Route::get('/register', function () {
    return view('register');
})->name('register');


Route::get('/assessment', [AssessmentController::class, 'index'])->name('assessment.index');
Route::post('/assessment', [AssessmentController::class, 'store'])->name('assessment.store');
Route::get('/results/{id}', [AssessmentController::class, 'results'])->name('assessment.results');
Route::post('/assessment/result', [AssessmentController::class, 'calculateBurnout'])->name('assessment.result');
Route::get('/assessment/result', [AssessmentController::class, 'showResultError'])->name('assessment.result.error');


// Admin routes - protected by auth middleware
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/view', [AdminController::class, 'report'])->name('admin.report');
Route::get('/view/programs', [AdminController::class, 'reportPrograms'])->name('admin.report.programs');
Route::get('/questions', [AdminController::class, 'questions'])->name('admin.questions');
Route::post('/questions/update', [QuestionController::class, 'updateQuestions'])->name('admin.questions.update');
Route::get('/files', [FileController::class, 'index'])->name('admin.files');
Route::post('/files/import', [FileController::class, 'importData'])->name('admin.import-data');
Route::get('/files/export', [FileController::class, 'exportData'])->name('admin.export-data');
Route::get('/files/download/{filename}', [FileController::class, 'downloadFile'])->name('admin.download-file');
Route::post('/files/delete/{filename}', [FileController::class, 'deleteFile'])->name('admin.delete-file');
Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
Route::post('/settings/user-settings', [AdminController::class, 'updateUserSettings'])->name('admin.update-user');
Route::post('/settings/clear-all-data', [AdminController::class, 'clearAllData'])->name('admin.clear-all-data');
Route::post('/admin/assessments/{id}', [AdminController::class, 'updateAssessment'])->name('admin.assessment.update');
Route::post('/admin/assessments/{id}/delete', [AdminController::class, 'deleteAssessment'])->name('admin.assessment.delete');
});