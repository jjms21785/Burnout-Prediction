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

    public function students()
    {
        $students = Assessment::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.students', compact('students'));
    }

    public function reports()
    {
        return view('admin.reports');
    }

    public function dataMonitoring(Request $request)
    {
        $query = Assessment::query();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('student_id', 'like', "%$search%")
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
                'student_id' => $a->student_id ?? '-',
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

    public function dataMonitoringPrograms()
    {
        $programs = Assessment::getUniquePrograms();
        return response()->json($programs);
    }

    public function topHighRiskStudents()
    {
        // Get top 5 students with highest risk, then by highest OLBI score
        $students = Assessment::where('overall_risk', 'high')
            ->orderByDesc('exhaustion_score')
            ->orderByDesc('disengagement_score')
            ->take(5)
            ->get()
            ->map(function($a) {
                return [
                    'student_id' => $a->student_id ?? 'N/A',
                    'name' => $a->name ?? '-',
                    'program' => $a->program ?? '-',
                    'risk' => ucfirst($a->overall_risk ?? '-'),
                    'score' => ($a->exhaustion_score + $a->disengagement_score) ?? '-',
                ];
            });
        return response()->json($students);
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,xlsx|max:10240', // 10MB max
        ]);
        $file = $request->file('import_file');
        $filename = 'import_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('imports', $filename);
        $imported = 0;
        try {
            if ($file->getClientOriginalExtension() === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                $header = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);
                    $this->importAssessmentRow($data);
                    $imported++;
                }
                fclose($handle);
            } elseif ($file->getClientOriginalExtension() === 'xlsx') {
                $rows = Excel::toArray([], $file)[0];
                $header = array_map('strtolower', $rows[0]);
                foreach (array_slice($rows, 1) as $row) {
                    $data = array_combine($header, $row);
                    $this->importAssessmentRow($data);
                    $imported++;
                }
            }
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
        return back()->with('success', "File uploaded and $imported records imported!");
    }

    private function importAssessmentRow($data)
    {
        // Map columns, use placeholder if missing
        Assessment::create([
            'student_id' => $data['student_id'] ?? 'N/A',
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
}