<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RecordsController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ViewController;

// Diagnostic route to check assets
Route::get('/check-assets', function () {
    $manifestPath = public_path('build/manifest.json');
    $manifestExists = file_exists($manifestPath);
    $buildDirExists = is_dir(public_path('build'));
    
    $files = [];
    if ($buildDirExists) {
        $files = array_slice(scandir(public_path('build')), 2);
    }
    
    return response()->json([
        'manifest_exists' => $manifestExists,
        'build_dir_exists' => $buildDirExists,
        'build_files' => $files,
        'manifest_path' => $manifestPath,
        'public_path' => public_path(),
    ]);
});

// Diagnostic route to test Flask API
Route::get('/test-flask', function () {
    $flaskUrl = env('FLASK_URL', 'http://127.0.0.1:5000');
    $testAnswers = array_fill(0, 30, 3); // Test with all 3s
    
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(10)->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post($flaskUrl . '/predict', [
            'all_answers' => $testAnswers
        ]);
        
        return response()->json([
            'flask_url' => $flaskUrl,
            'status' => $response->status(),
            'success' => $response->successful(),
            'response' => $response->json(),
            'error' => $response->failed() ? $response->body() : null
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'flask_url' => $flaskUrl,
            'error' => $e->getMessage(),
            'type' => get_class($e)
        ], 500);
    }
});

Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes

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


// Admin routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/records', [RecordsController::class, 'index'])->name('admin.records');
    Route::get('/records/programs', [RecordsController::class, 'programs'])->name('admin.records.programs');
    Route::post('/admin/assessments/{id}', [RecordsController::class, 'update'])->name('admin.assessment.update');
    Route::post('/admin/assessments/{id}/delete', [RecordsController::class, 'destroy'])->name('admin.assessment.delete');
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

    // Viewing assessment details
    Route::get('/admin/view/{id}', [ViewController::class, 'show'])->name('admin.view.show');
    Route::post('/admin/view/{id}/send-email', [ViewController::class, 'sendEmail'])->name('admin.view.send-email');
});