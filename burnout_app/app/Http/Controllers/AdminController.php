<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\QuestionController;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalAssessments = Assessment::count();
        
        // Burnout categories - calculate based on exhaustion/disengagement scores
        // Matching Python logic:
        // High exhaustion = exhaustion_score >= 18 (2.25 average * 8)
        // High disengagement = disengagement_score >= 17 (2.1 average * 8, rounded)
        // Category 0 (Low): neither high
        // Category 1 (Disengaged): high disengagement only
        // Category 2 (Exhausted): high exhaustion only
        // Category 3 (High Burnout): both high
        
        // High exhaustion threshold: 2.25 * 8 = 18
        // High disengagement threshold: 2.1 * 8 = 16.8, round to 17
        $highExhaustionThreshold = 18; // 2.25 average * 8
        $highDisengagementThreshold = 17; // 2.1 average * 8 (rounded)
        
        // Low Burnout (Category 0): neither high exhaustion nor high disengagement
        // Must have both scores available to be categorized
        $lowBurnout = Assessment::whereNotNull('exhaustion_score')
            ->whereNotNull('disengagement_score')
            ->where(function($q) use ($highExhaustionThreshold, $highDisengagementThreshold) {
                $q->where('exhaustion_score', '<', $highExhaustionThreshold)
                  ->where('disengagement_score', '<', $highDisengagementThreshold);
            })
            ->count();
        
        // Disengaged (Category 1): high disengagement but NOT high exhaustion
        // Must have both scores available
        $disengagement = Assessment::whereNotNull('exhaustion_score')
            ->whereNotNull('disengagement_score')
            ->where('disengagement_score', '>=', $highDisengagementThreshold)
            ->where('exhaustion_score', '<', $highExhaustionThreshold)
            ->count();
        
        // Exhausted (Category 2): high exhaustion but NOT high disengagement
        // Must have both scores available
        $exhaustion = Assessment::whereNotNull('exhaustion_score')
            ->whereNotNull('disengagement_score')
            ->where('exhaustion_score', '>=', $highExhaustionThreshold)
            ->where('disengagement_score', '<', $highDisengagementThreshold)
            ->count();
        
        // High Burnout (Category 3): both high exhaustion AND high disengagement
        // Must have both scores available
        $highBurnout = Assessment::whereNotNull('exhaustion_score')
            ->whereNotNull('disengagement_score')
            ->where('exhaustion_score', '>=', $highExhaustionThreshold)
            ->where('disengagement_score', '>=', $highDisengagementThreshold)
            ->count();

        // Age distribution
        $ageDistribution = Assessment::selectRaw('
            CASE 
                WHEN age BETWEEN 18 AND 20 THEN "18-20"
                WHEN age BETWEEN 21 AND 23 THEN "21-23"
                WHEN age BETWEEN 24 AND 26 THEN "24-26"
                ELSE "27+"
            END as age_group,
            COUNT(*) as count
        ')
        ->groupBy('age_group')
        ->get()
        ->pluck('count', 'age_group')
        ->toArray();

        // Gender distribution
        $genderDistribution = Assessment::selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->get()
            ->pluck('count', 'gender')
            ->toArray();

        // Year level distribution
        $yearDistribution = Assessment::selectRaw('year_level, COUNT(*) as count')
            ->groupBy('year_level')
            ->get()
            ->pluck('count', 'year_level')
            ->toArray();

        // Program distribution
        $programDistribution = Assessment::selectRaw('program, COUNT(*) as count')
            ->groupBy('program')
            ->get()
            ->pluck('count', 'program')
            ->toArray();

        // Latest submissions
        $latestSubmissions = Assessment::latest()
            ->take(5)
            ->get();

        // Question statistics - get all answers from assessments
        $questionStats = Assessment::whereNotNull('answers')
        ->get()
            ->map(function($assessment) {
                $answers = is_array($assessment->answers) ? $assessment->answers : json_decode($assessment->answers, true);
                return $answers ?? [];
            })
            ->filter();

        // Get questions from QuestionController (Q1-Q30)
        $questionController = new QuestionController();
        $questionsData = $questionController->getQuestions();
        
        // Ensure questions are sorted by question number
        usort($questionsData, function($a, $b) {
            $aNum = intval(preg_replace('/[^0-9]/', '', $a['id']));
            $bNum = intval(preg_replace('/[^0-9]/', '', $b['id']));
            return $aNum - $bNum;
        });
        
        // Extract Q1-Q30 questions text in order
        $questionsList = [];
        for ($i = 1; $i <= 30; $i++) {
            $qId = 'Q' . $i;
            $foundQuestion = collect($questionsData)->firstWhere('id', $qId);
            if ($foundQuestion) {
                $questionsList[] = $foundQuestion['text'];
            } else {
                // Fallback if question not found
                $questionsList[] = "Question $i";
            }
        }

        return view('admin.dashboard', compact(
            'totalAssessments',
            'highBurnout',
            'exhaustion',
            'disengagement',
            'lowBurnout',
            'ageDistribution',
            'genderDistribution',
            'yearDistribution',
            'programDistribution',
            'latestSubmissions',
            'questionStats',
            'questionsList'
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

            // Get all assessments without limit to show all uploaded data
            $assessments = $query->get();

            $data = $assessments->map(function($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->name ?? 'Unavailable',
                    'gender' => $a->gender ?? 'Unavailable',
                    'age' => $a->age ?? 'Unavailable',
                    'program' => $a->program ?? 'Unavailable',
                    'grade' => $a->year_level ?? 'Unavailable',
                    'risk' => $a->overall_risk ?? 'Unavailable',
                    'exhaustion_score' => $a->exhaustion_score ?? null,
                    'disengagement_score' => $a->disengagement_score ?? null,
                    'olbi_score' => ($a->exhaustion_score ?? 0) + ($a->disengagement_score ?? 0),
                    'confidence' => $a->confidence ?? null,
                    'last_update' => $a->updated_at ? $a->updated_at->format('M d') : 'Unavailable',
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

    public function questions()
    {
        // Use QuestionController to get questions (shared source)
        $questionController = new \App\Http\Controllers\QuestionController();
        $questions = $questionController->getQuestions();
        
        return view('admin.questions', compact('questions'));
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function clearAllData(Request $request)
    {
        try {
            // Delete all assessments from the database
            $deletedCount = Assessment::count();
            Assessment::truncate();
            
            return redirect()->route('admin.settings')->with('success', "All assessment data has been permanently deleted. {$deletedCount} record(s) were removed.");
        } catch (\Exception $e) {
            Log::error('Clear all data failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to clear data: ' . $e->getMessage());
        }
    }

    public function updateAssessment(Request $request, $id)
    {
        try {
            $assessment = Assessment::findOrFail($id);
            
            $assessment->update([
                'name' => $request->input('name', $assessment->name),
                'gender' => $request->input('gender', $assessment->gender),
                'age' => $request->input('age', $assessment->age),
                'program' => $request->input('program', $assessment->program),
                'year_level' => $request->input('year_level', $assessment->year_level),
                'overall_risk' => $request->input('overall_risk', $assessment->overall_risk),
            ]);

            return response()->json(['success' => true, 'message' => 'Assessment updated successfully']);
        } catch (\Exception $e) {
            Log::error('Assessment update failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update assessment: ' . $e->getMessage()], 500);
        }
    }

    public function deleteAssessment($id)
    {
        try {
            $assessment = Assessment::findOrFail($id);
            $assessment->delete();

            return response()->json(['success' => true, 'message' => 'Assessment deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Assessment deletion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete assessment: ' . $e->getMessage()], 500);
        }
    }
}