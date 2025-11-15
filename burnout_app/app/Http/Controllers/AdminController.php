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
        
        // Read burnout categories directly from stored ML predictions
        $assessments = Assessment::all();
        $lowBurnout = 0;
        $disengagement = 0;
        $exhaustion = 0;
        $highBurnout = 0;
        
        foreach ($assessments as $assessment) {
            // Get category from stored ML prediction (no manual calculation)
            $category = $assessment->getBurnoutCategoryLabel();
            
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
                // Get category from stored ML prediction (no manual calculation)
                return [
                    'assessment' => $assessment,
                    'category' => $assessment->getBurnoutCategoryLabel(),
                    'categoryColor' => $assessment->getBurnoutCategoryColor()
                ];
            });

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

        $featureImportance = [];
        $featureImportancePath = base_path('../random_forest/feature_importance.json');
        if (file_exists($featureImportancePath)) {
            $featureImportanceJson = file_get_contents($featureImportancePath);
            $featureImportance = json_decode($featureImportanceJson, true) ?? [];
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
            'featureImportance',
            'questionsList'
        ));
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


    // Note: Removed calculateBurnoutCategory() and calculateBurnoutCategoryWithScores() methods
    // All burnout categories now come directly from stored ML predictions using Assessment::getBurnoutCategoryLabel()
    
    /**
     * Calculate exhaustion and disengagement scores from answers
     * Used only for calculating scores, NOT for determining burnout category
     * Burnout category comes from stored ML prediction only
     */
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
