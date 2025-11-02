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
        // Use only new column names
        $lowBurnout = Assessment::whereNotNull('Exhaustion')
            ->whereNotNull('Disengagement')
            ->where('Exhaustion', '<', $highExhaustionThreshold)
            ->where('Disengagement', '<', $highDisengagementThreshold)
            ->count();
        
        // Disengaged (Category 1): high disengagement but NOT high exhaustion
        // Must have both scores available
        $disengagement = Assessment::whereNotNull('Exhaustion')
            ->whereNotNull('Disengagement')
            ->where('Disengagement', '>=', $highDisengagementThreshold)
            ->where('Exhaustion', '<', $highExhaustionThreshold)
            ->count();
        
        // Exhausted (Category 2): high exhaustion but NOT high disengagement
        // Must have both scores available
        $exhaustion = Assessment::whereNotNull('Exhaustion')
            ->whereNotNull('Disengagement')
            ->where('Exhaustion', '>=', $highExhaustionThreshold)
            ->where('Disengagement', '<', $highDisengagementThreshold)
            ->count();
        
        // High Burnout (Category 3): both high exhaustion AND high disengagement
        // Must have both scores available
        $highBurnout = Assessment::whereNotNull('Exhaustion')
            ->whereNotNull('Disengagement')
            ->where('Exhaustion', '>=', $highExhaustionThreshold)
            ->where('Disengagement', '>=', $highDisengagementThreshold)
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

        // Gender distribution - use new column name
        $genderDistribution = Assessment::selectRaw('sex, COUNT(*) as count')
            ->whereNotNull('sex')
            ->groupBy('sex')
            ->get()
            ->pluck('count', 'sex')
            ->filter(function($value, $key) {
                return !empty($key) && $value > 0;
            })
            ->toArray();

        // Year level distribution - use new column name
        $yearDistribution = Assessment::selectRaw('year, COUNT(*) as count')
            ->whereNotNull('year')
            ->groupBy('year')
            ->get()
            ->pluck('count', 'year')
            ->filter(function($value, $key) {
                return !empty($key) && $value > 0;
            })
            ->toArray();

        // Program distribution - use new column name
        $programDistribution = Assessment::selectRaw('college, COUNT(*) as count')
            ->whereNotNull('college')
            ->groupBy('college')
            ->get()
            ->pluck('count', 'college')
            ->filter(function($value, $key) {
                return !empty($key) && $value > 0;
            })
            ->toArray();

        // Latest submissions with calculated categories
        $latestSubmissions = Assessment::latest()
            ->take(5)
            ->get()
            ->map(function($assessment) {
                $categoryData = $this->calculateBurnoutCategory($assessment);
                return [
                    'assessment' => $assessment,
                    'category' => $categoryData['category'],
                    'categoryColor' => $categoryData['color']
                ];
            });

        // Question statistics - get all answers from assessments
        $questionStats = Assessment::whereNotNull('answers')
        ->get()
            ->map(function($assessment) {
                // Use raw_answers accessor for backward compatibility
                return $assessment->raw_answers ?? [];
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

            // Search - use new column names
            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('college', 'like', "%$search%")
                      ->orWhere('year', 'like', "%$search%")
                      ->orWhere('sex', 'like', "%$search%")
                      ->orWhere('age', 'like', "%$search%")
                      ->orWhere('Burnout_Category', 'like', "%$search%")
                      ;
                });
            }

            // Filters - use new column names
            if ($grade = $request->input('grade')) {
                $query->where('year', $grade);
            }
            if ($age = $request->input('age')) {
                $query->where('age', $age);
            }
            if ($gender = $request->input('gender')) {
                $query->where('sex', $gender);
            }
            if ($dept = $request->input('program')) {
                $query->where('college', $dept);
            }
            if ($risk = $request->input('risk')) {
                $query->where('Burnout_Category', strtolower($risk));
            }
            if ($time = $request->input('time')) {
                if ($time === '7days') {
                    $query->where('created_at', '>=', now()->subDays(7));
                } elseif ($time === 'month') {
                    $query->whereMonth('created_at', now()->month);
                } // Custom can be added
            }
            // Sorting - use new column name
            if ($request->input('olbi_sort')) {
                $sortDirection = $request->input('olbi_sort') === 'desc' ? 'desc' : 'asc';
                $query->orderBy('Exhaustion', $sortDirection);
            }
            if ($request->input('conf_sort')) {
                $query->orderBy('confidence', $request->input('conf_sort') === 'desc' ? 'desc' : 'asc');
            }
            // Default sort
            $query->orderByDesc('created_at');

            // Get all assessments without limit to show all uploaded data
            $assessments = $query->get();

            $data = $assessments->map(function($a) {
                // Use accessors for backward compatibility (accessors handle the mapping)
                // Accessors map: gender -> sex, program -> college, year_level -> year, etc.
                
                return [
                    'id' => $a->id,
                    'name' => $a->name ?? 'Unavailable',
                    'gender' => $a->gender ?? 'Unavailable', // Accessor maps sex -> gender
                    'age' => $a->age ?? 'Unavailable',
                    'program' => $a->program ?? 'Unavailable', // Accessor maps college -> program
                    'grade' => $a->year_level ?? 'Unavailable', // Accessor maps year -> year_level
                    'risk' => $a->overall_risk ?? 'Unavailable', // Accessor maps Burnout_Category -> overall_risk
                    'exhaustion_score' => $a->exhaustion_score ?? null, // Accessor maps Exhaustion -> exhaustion_score
                    'disengagement_score' => $a->disengagement_score ?? null, // Accessor maps Disengagement -> disengagement_score
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
            
            // Map input to correct database column names
            $updateData = [
                'name' => $request->input('name', $assessment->name),
                'age' => $request->input('age', $assessment->age),
            ];
            
            // Map gender to sex column
            if ($request->has('gender')) {
                $updateData['sex'] = $request->input('gender');
            } elseif ($assessment->sex) {
                $updateData['sex'] = $assessment->sex;
            }
            
            // Map program to college column
            if ($request->has('program')) {
                $updateData['college'] = $request->input('program');
            } elseif ($assessment->college) {
                $updateData['college'] = $assessment->college;
            }
            
            // Map year_level to year column
            if ($request->has('year_level')) {
                $updateData['year'] = $request->input('year_level');
            } elseif ($assessment->year) {
                $updateData['year'] = $assessment->year;
            }
            
            // Map overall_risk to Burnout_Category column
            if ($request->has('overall_risk')) {
                $updateData['Burnout_Category'] = $request->input('overall_risk');
            } elseif ($assessment->Burnout_Category) {
                $updateData['Burnout_Category'] = $assessment->Burnout_Category;
            }
            
            $assessment->update($updateData);

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

    /**
     * Calculate burnout category based on Exhaustion and Disengagement scores
     * 
     * @param \App\Models\Assessment $assessment
     * @return array ['category' => string, 'color' => string]
     */
    private function calculateBurnoutCategory($assessment)
    {
        $exhaustion = $assessment->Exhaustion ?? $assessment->exhaustion_score ?? null;
        $disengagement = $assessment->Disengagement ?? $assessment->disengagement_score ?? null;
        $highExhaustionThreshold = 18; // 2.25 average * 8
        $highDisengagementThreshold = 17; // 2.1 average * 8
        
        $category = 'Unknown';
        $categoryColor = 'bg-gray-100 text-gray-800';
        
        if ($exhaustion !== null && $disengagement !== null) {
            $highExhaustion = $exhaustion >= $highExhaustionThreshold;
            $highDisengagement = $disengagement >= $highDisengagementThreshold;
            
            if (!$highExhaustion && !$highDisengagement) {
                $category = 'Low Burnout';
                $categoryColor = 'bg-green-100 text-green-800';
            } elseif (!$highExhaustion && $highDisengagement) {
                $category = 'Disengaged';
                $categoryColor = 'bg-orange-100 text-orange-800';
            } elseif ($highExhaustion && !$highDisengagement) {
                $category = 'Exhausted';
                $categoryColor = 'bg-orange-100 text-orange-800';
            } else {
                $category = 'High Burnout';
                $categoryColor = 'bg-red-100 text-red-800';
            }
        } elseif ($assessment->overall_risk || $assessment->Burnout_Category) {
            // Fallback to stored category if scores not available
            $storedCategory = strtolower($assessment->overall_risk ?? $assessment->Burnout_Category ?? '');
            if ($storedCategory === 'high') {
                $category = 'High Burnout';
                $categoryColor = 'bg-red-100 text-red-800';
            } elseif ($storedCategory === 'moderate') {
                $category = 'Moderate';
                $categoryColor = 'bg-orange-100 text-orange-800';
            } elseif ($storedCategory === 'low') {
                $category = 'Low Burnout';
                $categoryColor = 'bg-green-100 text-green-800';
            }
        }
        
        return [
            'category' => $category,
            'color' => $categoryColor
        ];
    }
}