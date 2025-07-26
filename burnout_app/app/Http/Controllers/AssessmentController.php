<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\Http;

class AssessmentController extends Controller
{
    public function __construct() {}

    public function index()
    {
        $olbi_questions = [
            'I always find new and interesting aspects in my studies.', // D1P
            'There are days when I feel tired before I arrive in class or start studying.', // E1N
            'Over time, one can become disconnected from this type of study.', // D2N
            'I can usually manage my study-related workload well.', // E2P
            'I find my studies to be a positive challenge.', // D3P
            'After a class or after studying, I tend to need more time than in the past in order to relax and feel better.', // E3N
            'Lately, I tend to think less about my academic tasks and do them almost mechanically.', // D4N
            'I can tolerate the pressure of my studies very well.', // E4P
            'I feel more and more engaged in my studies.', // D5P
            'While studying, I usually feel emotionally drained.', // E5N
            'It happens more and more often that I talk about my studies in a negative way.', // D6N
            'After a class or after studying, I have enough energy for my leisure activities.', // E6P
            'This is the only field of study that I can imagine myself doing.', // D7P
            'After a class or after studying, I usually feel worn out and weary.', // E7N
            'Sometimes I feel sickened by my studies.', // D8N
            'When I study, I usually feel energized.' // E8P
        ];
        $programs = [
            'Accountancy',
            'Business Administration Major in Marketing Management',
            'Entrepreneurship',
            'Hospitality Management',
            'Information Technology',
            'Electronics Engineering',
            'Nursing',
            'Other'
        ];
        $year_levels = [
            '1st Year', '2nd Year', '3rd Year', '4th Year'
        ];
        $genders = ['Male', 'Female', 'Other'];
        return view('assessment.index', compact('olbi_questions', 'programs', 'year_levels', 'genders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'age' => ['required', 'integer', 'min:10', 'max:100'],
            'gender' => ['required', 'string', 'max:50'],
            'program' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9 ]+$/'],
            'answers' => 'required|array|size:16',
            'answers.*' => 'required|integer|min:0|max:3'
        ], [
            'name.regex' => 'Name may only contain letters and spaces.',
            'year_level.regex' => 'Year level may only contain letters, numbers, and spaces.'
        ]);

        // Handle program 'Other' custom input
        if ($request->program === 'Other' && $request->filled('program_other')) {
            $validated['program'] = $request->input('program_other');
        }

        // Assign Anonymous# if name is empty
        if (empty($validated['name'])) {
            $lastAnon = Assessment::where('name', 'like', 'Anonymous%')->orderByDesc('id')->first();
            $anonNum = 1;
            if ($lastAnon && preg_match('/Anonymous(\d+)/', $lastAnon->name, $m)) {
                $anonNum = intval($m[1]) + 1;
            }
            $validated['name'] = 'Anonymous' . $anonNum;
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

        // Apply reversal logic for negative items before sending to API
        $responses = [];
        foreach ($validated['answers'] as $i => $answer) {
            $responses['Q' . ($i + 1)] = (int) $answer;
        }
        
        // Reverse negative items (N): Q2, Q3, Q6, Q7, Q10, Q11, Q14, Q15
        $reverseItems = ['Q2', 'Q3', 'Q6', 'Q7', 'Q10', 'Q11', 'Q14', 'Q15'];
        foreach ($reverseItems as $item) {
            $responses[$item] = 5 - $responses[$item];
        }

        // Prepare input for model in training data order: D1P,D2N,D3P,D4N,D5P,D6N,D7P,D8N,E1N,E2P,E3N,E4P,E5N,E6P,E7N,E8P
        $modelInput = [
            $responses['Q1'],  // D1P
            $responses['Q3'],  // D2N 
            $responses['Q5'],  // D3P
            $responses['Q7'],  // D4N 
            $responses['Q9'],  // D5P
            $responses['Q11'], // D6N 
            $responses['Q13'], // D7P
            $responses['Q15'], // D8N 
            $responses['Q2'],  // E1N 
            $responses['Q4'],  // E2P
            $responses['Q6'],  // E3N 
            $responses['Q8'],  // E4P
            $responses['Q10'], // E5N 
            $responses['Q12'], // E6P
            $responses['Q14'], // E7N 
            $responses['Q16'], // E8P
        ];

        $response = Http::post('http://127.0.0.1:5000/predict', ['input' => $modelInput]);
        $json = $response->json();
        $prediction = strtolower($json['label'] ?? '');
        $confidence = $json['confidence'] ?? null;
        $exhaustion = $json['exhaustion'] ?? null;
        $disengagement = $json['disengagement'] ?? null;

        // Map prediction labels to database enum values
        $overallRisk = 'unknown';
        if ($prediction) {
            $label = strtolower($prediction);
            if (in_array($label, ['low', 'moderate', 'high'])) {
                $overallRisk = $label;
            } elseif (in_array($label, ['disengaged', 'exhausted'])) {
                $overallRisk = 'moderate'; // Map disengaged and exhausted to moderate
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
            'name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'age' => ['required', 'integer', 'min:10', 'max:100'],
            'gender' => ['required', 'string', 'max:50'],
            'program' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9 ]+$/'],
            'answers' => 'required|array|size:16',
            'answers.*' => 'required|integer|min:1|max:4'
        ], [
            'name.regex' => 'Name may only contain letters and spaces.',
            'year_level.regex' => 'Year level may only contain letters, numbers, and spaces.'
        ]);

        // Handle program 'Other' custom input
        if ($request->program === 'Other' && $request->filled('program_other')) {
            $validated['program'] = $request->input('program_other');
        }

        // Collect responses from answers array
        $answers = $validated['answers'];
        $responses = [];
        $original_responses = [];
        
        for ($i = 0; $i < 16; $i++) {
            $responses["Q" . ($i + 1)] = (int) ($answers[$i] ?? 0);
            $original_responses["Q" . ($i + 1)] = (int) ($answers[$i] ?? 0);
        }

        $name = $validated['name'];
        if (empty($name)) {
            $lastAnon = Assessment::where('name', 'like', 'Anonymous%')->orderByDesc('id')->first();
            $anonNum = 1;
            if ($lastAnon && preg_match('/Anonymous(\d+)/', $lastAnon->name, $m)) {
                $anonNum = intval($m[1]) + 1;
            }
            $name = 'Anonymous' . $anonNum;
        }
        $age = $validated['age'];
        $gender = $validated['gender'];
        $program = $validated['program'];
        $year_level = $validated['year_level'];

        // 1. Reverse all negative worded items based on new mapping
        // Negative items (N): Q2, Q3, Q6, Q7, Q10, Q11, Q14, Q15
        $reverseItems = ['Q2', 'Q3', 'Q6', 'Q7', 'Q10', 'Q11', 'Q14', 'Q15'];
        foreach ($reverseItems as $item) {
            $responses[$item] = 5 - $responses[$item];
        }

        // 2. Fetch exhaustion and disengagement items from reversed responses
        // Disengagement items (D): Q1, Q3, Q5, Q7, Q9, Q11, Q13, Q15
        // Exhaustion items (E): Q2, Q4, Q6, Q8, Q10, Q12, Q14, Q16
        $exhaustionItems = ['Q2', 'Q4', 'Q6', 'Q8', 'Q10', 'Q12', 'Q14', 'Q16'];
        $disengagementItems = ['Q1', 'Q3', 'Q5', 'Q7', 'Q9', 'Q11', 'Q13', 'Q15'];
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

        // 4. Prepare input for model in training data order: D1P,D2N,D3P,D4N,D5P,D6N,D7P,D8N,E1N,E2P,E3N,E4P,E5N,E6P,E7N,E8P
        $modelInput = [
            $responses['Q1'],  // D1P
            $responses['Q3'],  // D2N (already reversed)
            $responses['Q5'],  // D3P
            $responses['Q7'],  // D4N (already reversed)
            $responses['Q9'],  // D5P
            $responses['Q11'], // D6N (already reversed)
            $responses['Q13'], // D7P
            $responses['Q15'], // D8N (already reversed)
            $responses['Q2'],  // E1N (already reversed)
            $responses['Q4'],  // E2P
            $responses['Q6'],  // E3N (already reversed)
            $responses['Q8'],  // E4P
            $responses['Q10'], // E5N (already reversed)
            $responses['Q12'], // E6P
            $responses['Q14'], // E7N (already reversed)
            $responses['Q16'], // E8P
        ];

        // 5. Python API prediction
        $apiUrl = 'http://127.0.0.1:5000/predict';
        $labels = ['Low', 'Disengaged', 'Exhausted', 'High'];
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
            if (in_array($label, ['low', 'moderate', 'high'])) {
                $overallRisk = $label;
            } elseif (in_array($label, ['disengaged', 'exhausted'])) {
                $overallRisk = 'moderate'; // Map disengaged and exhausted to moderate
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
        
        // Apply reversal logic for negative items
        $reverseItems = ['Q2', 'Q3', 'Q6', 'Q7', 'Q10', 'Q11', 'Q14', 'Q15'];
        foreach ($reverseItems as $item) {
            $responses[$item] = 5 - $responses[$item];
        }
        
        // Calculate scores, averages, and categories
        $exhaustionItems = ['Q2', 'Q4', 'Q6', 'Q8', 'Q10', 'Q12', 'Q14', 'Q16'];
        $disengagementItems = ['Q1', 'Q3', 'Q5', 'Q7', 'Q9', 'Q11', 'Q13', 'Q15'];
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