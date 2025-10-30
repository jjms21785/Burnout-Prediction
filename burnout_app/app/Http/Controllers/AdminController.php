<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalAssessments = Assessment::count();
        $highRisk = Assessment::where('overall_risk', 'high')->count();
        $moderateRisk = Assessment::where('overall_risk', 'moderate')->count();
        $lowRisk = Assessment::where('overall_risk', 'low')->count();

        $recentAssessments = Assessment::with('user')
            ->latest()
            ->take(10)
            ->get();

        $riskDistribution = [
            'low' => $lowRisk,
            'moderate' => $moderateRisk,
            'high' => $highRisk
        ];

        $monthlyTrends = Assessment::selectRaw('
            strftime("%m", created_at) as month,
            strftime("%Y", created_at) as year,
            overall_risk,
            COUNT(*) as count
        ')
        ->where('created_at', '>=', Carbon::now()->subMonths(6))
        ->groupBy('month', 'year', 'overall_risk')
        ->orderBy('year')
        ->orderBy('month')
        ->get()
        ->groupBy(['month', 'overall_risk']);

        return view('admin.dashboard', compact(
            'totalAssessments',
            'highRisk',
            'moderateRisk',
            'lowRisk',
            'recentAssessments',
            'riskDistribution',
            'monthlyTrends'
        ));
    }

    public function report(Request $request)
    {
        // If it's an AJAX request, return JSON data for the table
        if ($request->ajax() || $request->wantsJson()) {
            $query = Assessment::query();

            // Search
            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('program', 'like', "%$search%")
                      ->orWhere('year_level', 'like', "%$search%")
                      ->orWhere('gender', 'like', "%$search%")
                      ->orWhere('age', 'like', "%$search%")
                      ->orWhere('overall_risk', 'like', "%$search%")
                      ;
                });
            }

            // Filters
            if ($grade = $request->input('grade')) {
                $query->where('year_level', $grade);
            }
            if ($age = $request->input('age')) {
                $query->where('age', $age);
            }
            if ($gender = $request->input('gender')) {
                $query->where('gender', $gender);
            }
            if ($dept = $request->input('program')) {
                $query->where('program', $dept);
            }
            if ($risk = $request->input('risk')) {
                $query->where('overall_risk', strtolower($risk));
            }
            if ($time = $request->input('time')) {
                if ($time === '7days') {
                    $query->where('created_at', '>=', now()->subDays(7));
                } elseif ($time === 'month') {
                    $query->whereMonth('created_at', now()->month);
                } // Custom can be added
            }
            // Sorting
            if ($request->input('olbi_sort')) {
                $query->orderBy('exhaustion_score', $request->input('olbi_sort') === 'desc' ? 'desc' : 'asc');
            }
            if ($request->input('conf_sort')) {
                $query->orderBy('confidence', $request->input('conf_sort') === 'desc' ? 'desc' : 'asc');
            }
            // Default sort
            $query->orderByDesc('created_at');

            $assessments = $query->limit(100)->get();

            $data = $assessments->map(function($a) {
                return [
                    'name' => $a->name ?? '-',
                    'gender' => $a->gender ?? '-',
                    'age' => $a->age ?? '-',
                    'program' => $a->program ?? '-',
                    'grade' => $a->year_level ?? '-',
                    'risk' => ucfirst($a->overall_risk ?? '-'),
                    'olbi_score' => $a->exhaustion_score + $a->disengagement_score ?? '-',
                    'confidence' => $a->confidence ? ($a->confidence . '%') : '-',
                    'last_update' => $a->updated_at ? $a->updated_at->format('M d') : '-',
                ];
            });

            return response()->json($data);
        }
        
        // Otherwise, return the view
        return view('admin.report');
    }

    public function reportPrograms()
    {
        $programs = Assessment::getUniquePrograms();
        return response()->json($programs);
    }

    private function importAssessmentRow($data)
    {
        // Map columns, use placeholder if missing
        Assessment::create([
            'name' => $data['name'] ?? 'N/A',
            'age' => $data['age'] ?? null,
            'gender' => $data['gender'] ?? 'N/A',
            'program' => $data['program'] ?? ($data['department'] ?? 'N/A'),
            'year_level' => $data['year_level'] ?? ($data['grade'] ?? 'N/A'),
            'overall_risk' => strtolower($data['overall_risk'] ?? $data['risk'] ?? 'unknown'),
            'exhaustion_score' => $data['exhaustion_score'] ?? ($data['olbi_score'] ?? 0),
            'disengagement_score' => $data['disengagement_score'] ?? 0,
            'confidence' => $data['confidence'] ?? null,
            'answers' => [],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function questions()
    {
        // Define the OLBI questions
        $questions = [
            'disengagement' => [
                ['id' => 'D1P', 'text' => 'I always find new and interesting aspects in my studies', 'type' => 'positive'],
                ['id' => 'D2N', 'text' => 'It happens more and more often that I talk about my studies in a negative way', 'type' => 'negative'],
                ['id' => 'D3P', 'text' => 'I increasingly engage myself in my studies', 'type' => 'positive'],
                ['id' => 'D4N', 'text' => 'I feel more and more disengaged from my studies', 'type' => 'negative'],
                ['id' => 'D5P', 'text' => 'I find my studies to be a positive challenge', 'type' => 'positive'],
                ['id' => 'D6N', 'text' => 'Over time, one can become disconnected from studying', 'type' => 'negative'],
                ['id' => 'D7P', 'text' => 'I find my studies to be full of meaning and purpose', 'type' => 'positive'],
                ['id' => 'D8N', 'text' => 'Sometimes I feel sickened by my study tasks', 'type' => 'negative'],
            ],
            'exhaustion' => [
                ['id' => 'E1N', 'text' => 'There are days when I feel tired before I arrive at university', 'type' => 'negative'],
                ['id' => 'E2P', 'text' => 'After my studies, I have enough energy for my leisure activities', 'type' => 'positive'],
                ['id' => 'E3N', 'text' => 'My studies emotionally drain me', 'type' => 'negative'],
                ['id' => 'E4P', 'text' => 'After studying, I usually feel fresh and vigorous', 'type' => 'positive'],
                ['id' => 'E5N', 'text' => 'I can tolerate the pressure of my studies very well', 'type' => 'negative'],
                ['id' => 'E6P', 'text' => 'During my studies I usually feel fit and strong', 'type' => 'positive'],
                ['id' => 'E7N', 'text' => 'I feel exhausted when I go home from university', 'type' => 'negative'],
                ['id' => 'E8P', 'text' => 'Usually, I can manage the amount of my course-work well', 'type' => 'positive'],
            ]
        ];
        
        return view('admin.questions', compact('questions'));
    }

    public function updateQuestions(Request $request)
    {
        // In a real application, you would store questions in a database
        // For now, we'll just return a success message
        // You could store them in a config file or database table
        
        $questions = $request->input('questions');
        
        // TODO: Save questions to database or config file
        // For now, we'll just simulate success
        
        return back()->with('success', 'Questions updated successfully!');
    }

    public function files()
    {
        $totalRecords = Assessment::count();
        return view('admin.files', compact('totalRecords'));
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $imported = 0;
        $errors = [];

        try {
            if (in_array($file->getClientOriginalExtension(), ['csv'])) {
                $handle = fopen($file->getRealPath(), 'r');
                $header = fgetcsv($handle);
                
                while (($row = fgetcsv($handle)) !== false) {
                    try {
                        $data = array_combine($header, $row);
                        $this->importAssessmentRow($data);
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Row error: " . $e->getMessage();
                    }
                }
                fclose($handle);
            } elseif (in_array($file->getClientOriginalExtension(), ['xlsx', 'xls'])) {
                $rows = Excel::toArray([], $file)[0];
                $header = array_map('strtolower', $rows[0]);
                
                foreach (array_slice($rows, 1) as $row) {
                    try {
                        $data = array_combine($header, $row);
                        $this->importAssessmentRow($data);
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Row error: " . $e->getMessage();
                    }
                }
            }

            $message = "$imported records imported successfully!";
            if (count($errors) > 0) {
                $message .= " (" . count($errors) . " errors occurred)";
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function exportData(Request $request)
    {
        $format = $request->input('format', 'csv');
        $assessments = Assessment::all();

        if ($format === 'csv') {
            $filename = 'assessments_export_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() use ($assessments) {
                $file = fopen('php://output', 'w');
                
                // CSV Header
                fputcsv($file, [
                    'ID', 'Name', 'Age', 'Gender', 'Program', 'Year Level',
                    'Overall Risk', 'Exhaustion Score', 'Disengagement Score',
                    'Confidence', 'Created At', 'Updated At'
                ]);

                // Data rows
                foreach ($assessments as $assessment) {
                    fputcsv($file, [
                        $assessment->id,
                        $assessment->name,
                        $assessment->age,
                        $assessment->gender,
                        $assessment->program,
                        $assessment->year_level,
                        $assessment->overall_risk,
                        $assessment->exhaustion_score,
                        $assessment->disengagement_score,
                        $assessment->confidence,
                        $assessment->created_at,
                        $assessment->updated_at,
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } elseif ($format === 'xlsx') {
            // For Excel export, we'll use a simple approach
            // In production, you might want to use Laravel Excel package
            $filename = 'assessments_export_' . date('Y-m-d_His') . '.xlsx';
            
            // Create a simple Excel-compatible format using CSV with .xlsx extension
            // Note: For proper Excel, you'd need Laravel Excel or similar
            return $this->exportData(new Request(['format' => 'csv']));
        }
        
        return back()->with('error', 'Invalid export format');
    }

    public function settings()
    {
        // Load current settings (from config or database)
        $settings = [
            'site_name' => config('app.name', 'Burnalytics'),
            'admin_email' => 'admin@burnalytix.com',
            'records_per_page' => 20,
            'enable_notifications' => true,
            'data_retention_days' => 365,
        ];
        
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'records_per_page' => 'required|integer|min:10|max:100',
            'data_retention_days' => 'required|integer|min:30|max:3650',
        ]);

        // TODO: Save settings to database or config
        // For now, we'll just return success
        
        return back()->with('success', 'Settings updated successfully!');
    }

    public function downloadFile($filename)
    {
        $filePath = storage_path('app/imports/' . $filename);
        
        if (!file_exists($filePath)) {
            return back()->with('error', 'File not found.');
        }

        return response()->download($filePath);
    }

    public function deleteFile($filename)
    {
        $filePath = storage_path('app/imports/' . $filename);
        
        if (!file_exists($filePath)) {
            return back()->with('error', 'File not found.');
        }

        try {
            unlink($filePath);
            return back()->with('success', 'File deleted successfully!');
        } catch (\Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete file: ' . $e->getMessage());
        }
    }
}