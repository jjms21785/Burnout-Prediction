<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\QuestionController;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalAssessments = Assessment::count();
        
        $highExhaustionThreshold = 18;
        $highDisengagementThreshold = 17;
        
        $assessments = Assessment::all();
        $lowBurnout = 0;
        $disengagement = 0;
        $exhaustion = 0;
        $highBurnout = 0;
        
        foreach ($assessments as $assessment) {
            $categoryData = $this->calculateBurnoutCategoryWithScores($assessment);
            $category = $categoryData['category'];
            
            if ($category === 'Low Burnout') {
                $lowBurnout++;
            } elseif ($category === 'Disengaged') {
                $disengagement++;
            } elseif ($category === 'Exhausted') {
                $exhaustion++;
            } elseif ($category === 'High Burnout') {
                $highBurnout++;
            }
        }

        $ageDistribution = Assessment::selectRaw('
            CASE 
                WHEN age BETWEEN 18 AND 20 THEN "18-20"
                WHEN age BETWEEN 21 AND 23 THEN "21-23"
                WHEN age BETWEEN 24 AND 26 THEN "24-26"
                ELSE NULL
            END as age_group,
            COUNT(*) as count
        ')
        ->groupBy('age_group')
        ->get()
        ->pluck('count', 'age_group')
        ->toArray();

        $genderDistribution = Assessment::selectRaw('sex, COUNT(*) as count')
            ->whereNotNull('sex')
            ->groupBy('sex')
            ->get()
            ->pluck('count', 'sex')
            ->filter(function($value, $key) {
                return !empty($key) && $value > 0;
            })
            ->toArray();

        $yearDistribution = Assessment::selectRaw('year, COUNT(*) as count')
            ->whereNotNull('year')
            ->groupBy('year')
            ->get()
            ->pluck('count', 'year')
            ->filter(function($value, $key) {
                return !empty($key) && $value > 0;
            })
            ->toArray();

        $programDistribution = Assessment::selectRaw('college, COUNT(*) as count')
            ->whereNotNull('college')
            ->groupBy('college')
            ->get()
            ->pluck('count', 'college')
            ->filter(function($value, $key) {
                return !empty($key) && $value > 0;
            })
            ->toArray();

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

        $questionStats = Assessment::whereNotNull('answers')
        ->get()
            ->map(function($assessment) {
                return $assessment->raw_answers ?? [];
            })
            ->filter();

        $questionController = new QuestionController();
        $questionsData = $questionController->getQuestions();
        
        usort($questionsData, function($a, $b) {
            $aNum = intval(preg_replace('/[^0-9]/', '', $a['id']));
            $bNum = intval(preg_replace('/[^0-9]/', '', $b['id']));
            return $aNum - $bNum;
        });
        
        $questionsList = [];
        for ($i = 1; $i <= 30; $i++) {
            $qId = 'Q' . $i;
            $foundQuestion = collect($questionsData)->firstWhere('id', $qId);
            if ($foundQuestion) {
                $questionsList[] = $foundQuestion['text'];
            } else {
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
        if ($request->ajax() || $request->wantsJson()) {
            $query = Assessment::query();

            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('college', 'like', "%$search%")
                      ->orWhere('year', 'like', "%$search%")
                      ->orWhere('sex', 'like', "%$search%")
                      ->orWhere('age', 'like', "%$search%")
                      ->orWhere('Burnout_Category', 'like', "%$search%");
                });
            }

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
                }
            }
            if ($request->input('olbi_sort')) {
                $sortDirection = $request->input('olbi_sort') === 'desc' ? 'desc' : 'asc';
                $query->orderBy('Exhaustion', $sortDirection);
            }
            if ($request->input('conf_sort')) {
                $query->orderBy('confidence', $request->input('conf_sort') === 'desc' ? 'desc' : 'asc');
            }
            $query->orderByDesc('created_at');

            $assessments = $query->get();

            $data = $assessments->map(function($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->name ?? 'Unavailable',
                    'gender' => $a->sex ?? 'Unavailable',
                    'age' => $a->age ?? 'Unavailable',
                    'program' => $a->college ?? 'Unavailable',
                    'grade' => $a->year ?? 'Unavailable',
                    'risk' => $a->Burnout_Category ?? 'Unavailable',
                    'exhaustion_score' => $a->Exhaustion ?? null,
                    'disengagement_score' => $a->Disengagement ?? null,
                    'olbi_score' => ($a->Exhaustion ?? 0) + ($a->Disengagement ?? 0),
                    'confidence' => $a->confidence ?? null,
                    'last_update' => $a->updated_at ? $a->updated_at->format('M d') : 'Unavailable',
                ];
            });

            return response()->json($data);
        }
        
        return view('admin.report');
    }

    public function reportPrograms()
    {
        $programs = Assessment::getUniquePrograms();
        return response()->json($programs);
    }

    public function questions()
    {
        $questionController = new \App\Http\Controllers\QuestionController();
        $questions = $questionController->getQuestions();
        
        return view('admin.questions', compact('questions'));
    }

    public function settings()
    {
        $user = Auth::user();
        return view('admin.settings', compact('user'));
    }

    public function updateUserSettings(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'current_password' => ['required'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
        }

        try {
            $user->email = $request->email;
            
            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->new_password);
            }
            
            $user->save();

            return redirect()->route('admin.settings')->with('success', 'User settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('User settings update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update user settings: ' . $e->getMessage())->withInput();
        }
    }

    public function clearAllData(Request $request)
    {
        try {
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
            
            $updateData = [
                'name' => $request->input('name', $assessment->name),
                'age' => $request->input('age', $assessment->age),
            ];
            
            if ($request->has('gender')) {
                $updateData['sex'] = $request->input('gender');
            } elseif ($assessment->sex) {
                $updateData['sex'] = $assessment->sex;
            }
            
            if ($request->has('program')) {
                $updateData['college'] = $request->input('program');
            } elseif ($assessment->college) {
                $updateData['college'] = $assessment->college;
            }
            
            if ($request->has('year_level')) {
                $updateData['year'] = $request->input('year_level');
            } elseif ($assessment->year) {
                $updateData['year'] = $assessment->year;
            }
            
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

    private function calculateBurnoutCategory($assessment)
    {
        return $this->calculateBurnoutCategoryWithScores($assessment);
    }
    
    private function calculateBurnoutCategoryWithScores($assessment)
    {
        $exhaustion = $assessment->Exhaustion ?? null;
        $disengagement = $assessment->Disengagement ?? null;
        $highExhaustionThreshold = 18;
        $highDisengagementThreshold = 17;
        
        if ($exhaustion === null || $disengagement === null) {
            $scores = $this->calculateScoresFromAnswers($assessment);
            if ($exhaustion === null && isset($scores['exhaustion'])) {
                $exhaustion = $scores['exhaustion'];
            }
            if ($disengagement === null && isset($scores['disengagement'])) {
                $disengagement = $scores['disengagement'];
            }
        }
        
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
        } elseif ($assessment->Burnout_Category !== null) {
            $categoryNum = is_numeric($assessment->Burnout_Category) 
                ? (int)$assessment->Burnout_Category 
                : null;
            
            if ($categoryNum !== null && $categoryNum >= 0 && $categoryNum <= 3) {
                switch ($categoryNum) {
                    case 0:
                        $category = 'Low Burnout';
                        $categoryColor = 'bg-green-100 text-green-800';
                        break;
                    case 1:
                        $category = 'Disengaged';
                        $categoryColor = 'bg-orange-100 text-orange-800';
                        break;
                    case 2:
                        $category = 'Exhausted';
                        $categoryColor = 'bg-orange-100 text-orange-800';
                        break;
                    case 3:
                        $category = 'High Burnout';
                        $categoryColor = 'bg-red-100 text-red-800';
                        break;
                }
            }
        }
        
        return [
            'category' => $category,
            'color' => $categoryColor
        ];
    }
    
    private function calculateScoresFromAnswers($assessment)
    {
        $answers = $assessment->raw_answers ?? [];
        
        if (!is_array($answers) || count($answers) < 30) {
            return ['exhaustion' => null, 'disengagement' => null];
        }
        
        $exhaustionItems = [15, 16, 19, 20, 22, 24, 27, 28];
        $disengagementItems = [14, 17, 18, 21, 23, 25, 26, 29];
        
        $exhaustionScore = 0;
        $disengagementScore = 0;
        $hasExhaustionAnswers = false;
        $hasDisengagementAnswers = false;
        
        foreach ($exhaustionItems as $idx) {
            if (isset($answers[$idx]) && $answers[$idx] !== null && is_numeric($answers[$idx])) {
                $exhaustionScore += (int)$answers[$idx];
                $hasExhaustionAnswers = true;
            }
        }
        
        foreach ($disengagementItems as $idx) {
            if (isset($answers[$idx]) && $answers[$idx] !== null && is_numeric($answers[$idx])) {
                $disengagementScore += (int)$answers[$idx];
                $hasDisengagementAnswers = true;
            }
        }
        
        return [
            'exhaustion' => $hasExhaustionAnswers ? $exhaustionScore : null,
            'disengagement' => $hasDisengagementAnswers ? $disengagementScore : null
        ];
    }
}
