<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\QuestionController;

class AssessmentController extends Controller
{
    protected $questionController;

    public function __construct(QuestionController $questionController)
    {
        $this->questionController = $questionController;
    }

    public function index()
    {
        // Get questions from QuestionController (shared source)
        $questions = $this->questionController->getQuestionsForAssessment();
        
        $programs = [
            'College of Business and Accountancy',
            'College of Computer Studies',
            'College of Education',
            'College of Engineering',
            'College of Hospitality Management',
            'College of Nursing',
            'College of Art and Science'
        ];
        
        $year_levels = [
            '1st Year', '2nd Year', '3rd Year', '4th Year'
        ];
        
        $genders = ['Male', 'Female'];
        
        return view('assessment.index', compact('questions', 'programs', 'year_levels', 'genders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'age' => ['required', 'integer', 'min:10', 'max:100'],
            'gender' => ['required', 'string', 'max:50'],
            'program' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9 ]+$/'],
            'answers' => 'required|array|size:30',
            'answers.*' => 'required|integer|min:0|max:4'
        ], [
            'first_name.regex' => 'First name may only contain letters and spaces.',
            'last_name.regex' => 'Last name may only contain letters and spaces.',
            'year_level.regex' => 'Year level may only contain letters, numbers, and spaces.'
        ]);

        // Combine first_name and last_name into name field
        $firstName = trim($validated['first_name'] ?? '');
        $lastName = trim($validated['last_name'] ?? '');
        
        // Assign Anonymous# if both names are empty
        if (empty($firstName) && empty($lastName)) {
            $lastAnon = Assessment::where('name', 'like', 'Anonymous%')->orderByDesc('id')->first();
            $anonNum = 1;
            if ($lastAnon && preg_match('/Anonymous(\d+)/', $lastAnon->name, $m)) {
                $anonNum = intval($m[1]) + 1;
            }
            $validated['name'] = 'Anonymous' . $anonNum;
        } else {
            $validated['name'] = trim($firstName . ' ' . $lastName);
        }

        $assessment = Assessment::create([
            'name' => $validated['name'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'program' => $validated['program'],
            'year_level' => $validated['year_level'],
            'answers' => json_encode($validated['answers']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Collect responses (values are already correct from form, no reversal needed)
        $responses = [];
        foreach ($validated['answers'] as $i => $answer) {
            $responses['Q' . ($i + 1)] = (int) $answer;
        }

        // Calculate exhaustion and disengagement using only OLBI questions (Q15-Q30)
        // Exhaustion: Q16, Q17, Q20, Q21, Q23, Q25, Q28, Q29
        // Disengagement: Q15, Q18, Q19, Q22, Q24, Q26, Q27, Q30
        $exhaustionItems = ['Q16', 'Q17', 'Q20', 'Q21', 'Q23', 'Q25', 'Q28', 'Q29'];
        $disengagementItems = ['Q15', 'Q18', 'Q19', 'Q22', 'Q24', 'Q26', 'Q27', 'Q30'];
        
        $exhaustionScore = array_sum(array_intersect_key($responses, array_flip($exhaustionItems)));
        $disengagementScore = array_sum(array_intersect_key($responses, array_flip($disengagementItems)));

        // Prepare input for model - using OLBI questions in order: Disengagement then Exhaustion
        // Disengagement: Q15, Q18, Q19, Q22, Q24, Q26, Q27, Q30
        // Exhaustion: Q16, Q17, Q20, Q21, Q23, Q25, Q28, Q29
        $modelInput = [
            $responses['Q15'], // D1
            $responses['Q18'], // D2
            $responses['Q19'], // D3
            $responses['Q22'], // D4
            $responses['Q24'], // D5
            $responses['Q26'], // D6
            $responses['Q27'], // D7
            $responses['Q30'], // D8
            $responses['Q16'], // E1
            $responses['Q17'], // E2
            $responses['Q20'], // E3
            $responses['Q21'], // E4
            $responses['Q23'], // E5
            $responses['Q25'], // E6
            $responses['Q28'], // E7
            $responses['Q29'], // E8
        ];

        $response = Http::post('http://127.0.0.1:5000/predict', ['input' => $modelInput]);
        $json = $response->json();
        $prediction = strtolower($json['label'] ?? '');
        $confidence = $json['confidence'] ?? null;
        $exhaustion = $json['exhaustion'] ?? null;
        $disengagement = $json['disengagement'] ?? null;

        // prediction labels to database enum values
        $overallRisk = 'unknown';
        if ($prediction) {
            $label = strtolower($prediction);
            if (in_array($label, ['low', 'moderate', 'high'])) {
                $overallRisk = $label;
            } elseif (in_array($label, ['disengaged', 'exhausted'])) {
                $overallRisk = 'moderate'; // disengaged and exhausted to moderate
            }
        }
        $assessment->update([
            'overall_risk' => $overallRisk,
            'confidence' => is_array($confidence) ? max($confidence) : ($confidence ?: null),
            'exhaustion_score' => $exhaustion,
            'disengagement_score' => $disengagement
        ]);

        return redirect()->route('assessment.results', $assessment->id);
    }

    public function calculateBurnout(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'age' => ['required', 'integer', 'min:10', 'max:100'],
            'gender' => ['required', 'string', 'max:50'],
            'program' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9 ]+$/'],
            'answers' => 'required|array|size:30',
            'answers.*' => 'required|integer|min:0|max:4'
        ], [
            'first_name.regex' => 'First name may only contain letters and spaces.',
            'last_name.regex' => 'Last name may only contain letters and spaces.',
            'year_level.regex' => 'Year level may only contain letters, numbers, and spaces.'
        ]);

        // Collect responses from answers array
        $answers = $validated['answers'];
        $responses = [];
        $original_responses = [];
        
        for ($i = 0; $i < 16; $i++) {
            $responses["Q" . ($i + 1)] = (int) ($answers[$i] ?? 0);
            $original_responses["Q" . ($i + 1)] = (int) ($answers[$i] ?? 0);
        }

        // Combine first_name and last_name into name field
        $firstName = trim($validated['first_name'] ?? '');
        $lastName = trim($validated['last_name'] ?? '');
        
        // Assign Anonymous# if both names are empty
        if (empty($firstName) && empty($lastName)) {
            $lastAnon = Assessment::where('name', 'like', 'Anonymous%')->orderByDesc('id')->first();
            $anonNum = 1;
            if ($lastAnon && preg_match('/Anonymous(\d+)/', $lastAnon->name, $m)) {
                $anonNum = intval($m[1]) + 1;
            }
            $name = 'Anonymous' . $anonNum;
        } else {
            $name = trim($firstName . ' ' . $lastName);
        }
        $age = $validated['age'];
        $gender = $validated['gender'];
        $program = $validated['program'];
        $year_level = $validated['year_level'];

        // Calculate exhaustion and disengagement using only OLBI questions (Q15-Q30)
        // Exhaustion: Q16, Q17, Q20, Q21, Q23, Q25, Q28, Q29
        // Disengagement: Q15, Q18, Q19, Q22, Q24, Q26, Q27, Q30
        $exhaustionItems = ['Q16', 'Q17', 'Q20', 'Q21', 'Q23', 'Q25', 'Q28', 'Q29'];
        $disengagementItems = ['Q15', 'Q18', 'Q19', 'Q22', 'Q24', 'Q26', 'Q27', 'Q30'];
        $exhaustionScore = array_sum(array_intersect_key($responses, array_flip($exhaustionItems)));
        $disengagementScore = array_sum(array_intersect_key($responses, array_flip($disengagementItems)));

        // 3. Calculate averages and categories
        $exhaustionAverage = count($exhaustionItems) > 0 ? $exhaustionScore / count($exhaustionItems) : 0;
        $disengagementAverage = count($disengagementItems) > 0 ? $disengagementScore / count($disengagementItems) : 0;
        
        // 4. Determine categories based on thresholds
        $exhaustionCategory = $exhaustionAverage >= 2.25 ? 'High' : 'Low';
        $disengagementCategory = $disengagementAverage >= 2.10 ? 'High' : 'Low';

        // 5. Total score is the sum of exhaustion and disengagement items
        $totalScore = $exhaustionScore + $disengagementScore;

        // Prepare input for model - using OLBI questions in order: Disengagement then Exhaustion
        // Disengagement: Q15, Q18, Q19, Q22, Q24, Q26, Q27, Q30
        // Exhaustion: Q16, Q17, Q20, Q21, Q23, Q25, Q28, Q29
        $modelInput = [
            $responses['Q15'], // D1
            $responses['Q18'], // D2
            $responses['Q19'], // D3
            $responses['Q22'], // D4
            $responses['Q24'], // D5
            $responses['Q26'], // D6
            $responses['Q27'], // D7
            $responses['Q30'], // D8
            $responses['Q16'], // E1
            $responses['Q17'], // E2
            $responses['Q20'], // E3
            $responses['Q21'], // E4
            $responses['Q23'], // E5
            $responses['Q25'], // E6
            $responses['Q28'], // E7
            $responses['Q29'], // E8
        ];

        // 5. Python API prediction
        $apiUrl = 'http://127.0.0.1:5000/predict';
        $labels = ['Non-Burnout', 'Disengaged', 'Exhausted', 'BURNOUT'];
        $errorMsg = null;
        $predictedLabel = null;
        $confidence = null;
        try {
            $response = \Illuminate\Support\Facades\Http::post($apiUrl, ['input' => $modelInput]);
            if ($response->failed()) {
                $errorMsg = 'Prediction service unavailable.';
            } else {
                $result = $response->json();
                $predictedLabel = $result['label'] ?? null;
                $confidence = $result['confidence'] ?? null;
                $totalScore = $exhaustionScore + $disengagementScore;
                $exhaustionScore = $exhaustionScore;
                $disengagementScore = $disengagementScore;
            }
        } catch (\Exception $e) {
            $errorMsg = 'Prediction error: ' . $e->getMessage();
        }

        // Map prediction labels to database enum values
        $overallRisk = 'unknown';
        if ($predictedLabel) {
            $label = strtolower($predictedLabel);
            if (in_array($label, ['Non-Burnout', 'Disengaged', 'Exhausted', 'BURNOUT'])) {
                $overallRisk = $label;
            } elseif (in_array($label, ['Disengaged', 'Exhausted'])) {
                $overallRisk = 'Disengaged'; // Map disengaged and exhausted to moderate
            } elseif (in_array($label, ['Exhausted'])) {
                $overallRisk = 'Exhausted'; // Map exhausted to exhausted
            } elseif (in_array($label, ['BURNOUT'])) {
                $overallRisk = 'BURNOUT'; // Map burnout to burnout
            }
        }

        // Save assessment to database
        $assessment = Assessment::create([
            'name' => $name,
            'age' => $age,
            'gender' => $gender,
            'program' => $program,
            'year_level' => $year_level,
            'answers' => json_encode($original_responses),
            'overall_risk' => $overallRisk,
            'confidence' => is_array($confidence) ? max($confidence) : ($confidence ?: null),
            'exhaustion_score' => $exhaustionScore,
            'disengagement_score' => $disengagementScore,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return view('assessment.result', compact(
            'responses', 'original_responses', 'name', 'age', 'gender', 'program', 'year_level',
            'totalScore', 'predictedLabel', 'confidence', 'labels',
            'exhaustionScore', 'disengagementScore', 'exhaustionItems', 'disengagementItems', 'errorMsg', 'overallRisk',
            'exhaustionAverage', 'disengagementAverage', 'exhaustionCategory', 'disengagementCategory'
        ));
    }

    public function results($id)
    {
        $assessment = Assessment::findOrFail($id);
        
        // Decode the stored answers and apply reversal logic
        $original_responses = json_decode($assessment->answers, true);
        $responses = [];
        
        // Convert to Q1, Q2, etc. format
        foreach ($original_responses as $i => $answer) {
            $responses['Q' . ($i + 1)] = (int) $answer;
        }
        
        // Calculate scores, averages, and categories using only OLBI questions (Q15-Q30)
        // Exhaustion: Q16, Q17, Q20, Q21, Q23, Q25, Q28, Q29
        // Disengagement: Q15, Q18, Q19, Q22, Q24, Q26, Q27, Q30
        $exhaustionItems = ['Q16', 'Q17', 'Q20', 'Q21', 'Q23', 'Q25', 'Q28', 'Q29'];
        $disengagementItems = ['Q15', 'Q18', 'Q19', 'Q22', 'Q24', 'Q26', 'Q27', 'Q30'];
        $exhaustionScore = array_sum(array_intersect_key($responses, array_flip($exhaustionItems)));
        $disengagementScore = array_sum(array_intersect_key($responses, array_flip($disengagementItems)));
        
        $exhaustionAverage = count($exhaustionItems) > 0 ? $exhaustionScore / count($exhaustionItems) : 0;
        $disengagementAverage = count($disengagementItems) > 0 ? $disengagementScore / count($disengagementItems) : 0;
        
        $exhaustionCategory = $exhaustionAverage >= 2.25 ? 'High' : 'Low';
        $disengagementCategory = $disengagementAverage >= 2.10 ? 'High' : 'Low';
        
        $totalScore = $exhaustionScore + $disengagementScore;
        
        // Pass demographic data to the view
        $name = $assessment->name;
        $age = $assessment->age;
        $gender = $assessment->gender;
        $program = $assessment->program;
        $year_level = $assessment->year_level;
        
        return view('assessment.result', compact(
            'assessment', 'responses', 'original_responses', 
            'name', 'age', 'gender', 'program', 'year_level',
            'exhaustionScore', 'disengagementScore', 'totalScore',
            'exhaustionAverage', 'disengagementAverage', 'exhaustionCategory', 'disengagementCategory'
        ));
    }
}