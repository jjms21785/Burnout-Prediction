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
            'I always find new and interesting aspects in my studies.', // D1
            'There are days when I feel tired before I arrive in class or start studying.', // E1 - N
            'I can usually manage my study-related workload well.', // D2 -N
            'Over time, one can become disconnected from this type of study.', // E2
            'I find my studies to be a positive challenge.', // D3
            'After a class or after studying, I tend to need more time than in the past in order to relax and feel better.', // E3 - N
            'I can tolerate the pressure of my studies very well.', // D4 - N
            'Lately, I tend to think less about my academic tasks and do them almost mechanically.', // E4
            'I feel more and more engaged in my studies.', // D5
            'While studying, I usually feel emotionally drained.', // E5 - N
            'After a class or after studying, I have enough energy for my leisure activities.', // D6 -N
            'It happens more and more often that I talk about my studies in a negative way.', // E6
            'This is the only field of study that I can imagine myself doing.', // D7
            'After a class or after studying, I usually feel worn out and weary.', // E7 - N
            'When I study, I usually feel energized.', // D8 - N
            'Sometimes I feel sickened by my studies.' // E8
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
            'student_id' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9\-]+$/'],
            'name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'year_level' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9 ]+$/'],
            'answers' => 'required|array|size:16',
            'answers.*' => 'required|integer|min:0|max:3'
        ], [
            'student_id.regex' => 'Student ID may only contain letters, numbers, and dashes.',
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
            'student_id' => $validated['student_id'],
            'name' => $validated['name'],
            'year_level' => $validated['year_level'],
            'answers' => json_encode($validated['answers']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $payload = [];
        foreach ($validated['answers'] as $i => $answer) {
            $payload['Q' . ($i + 1)] = $answer;
        }

        $response = Http::post('http://127.0.0.1:5000/predict', $payload);
        $json = $response->json();
        $prediction = strtolower($json['prediction'] ?? '');
        $confidence = $json['confidence'] ?? null;
        $exhaustion = $json['olbi_s']['exhaustion'] ?? null;
        $disengagement = $json['olbi_s']['disengagement'] ?? null;

        $overallRisk = $prediction ? strtolower($prediction) : 'unknown';
        $assessment->update([
            'overall_risk' => $overallRisk,
            'confidence' => $confidence,
            'exhaustion_score' => $exhaustion,
            'disengagement_score' => $disengagement
        ]);

        return redirect()->route('assessment.results', $assessment->id);
    }

    public function calculateBurnout(Request $request)
    {
        // Collect responses
        $responses = [];
        for ($i = 1; $i <= 16; $i++) {
            $responses["Q$i"] = (int) $request->input("Q$i");
        }
        $original_responses = $responses;
        $student_id = $request->input('student_id');
        $name = $request->input('name');
        $year_level = $request->input('year_level');

        // 1. Reverse all negative worded items ['Q2', 'Q3', 'Q6', 'Q7', 'Q10', 'Q11', 'Q14', 'Q15']
        $reverseItems = ['Q2', 'Q3', 'Q6', 'Q7', 'Q10', 'Q11', 'Q14', 'Q15'];
        foreach ($reverseItems as $item) {
            $responses[$item] = 5 - $responses[$item];
        }

        // 2. Fetch exhaustion and disengagement items from reversed responses
        $exhaustionItems = ['Q2', 'Q4', 'Q6', 'Q8', 'Q10', 'Q12', 'Q14', 'Q16'];
        $disengagementItems = ['Q1', 'Q3', 'Q5', 'Q7', 'Q9', 'Q11', 'Q13', 'Q15'];
        $exhaustionScore = array_sum(array_intersect_key($responses, array_flip($exhaustionItems)));
        $disengagementScore = array_sum(array_intersect_key($responses, array_flip($disengagementItems)));

        // 3. Total score is the sum of exhaustion and disengagement items
        $totalScore = $exhaustionScore + $disengagementScore;

        // 4. Prepare input for model (D1,D2,D3,D4,D5,D6,D7,D8,E1,E2,E3,E4,E5,E6,E7,E8)
        $modelInput = [
            $responses['Q1'],  // D1
            $responses['Q3'],  // D2
            $responses['Q5'],  // D3
            $responses['Q7'],  // D4
            $responses['Q9'],  // D5
            $responses['Q11'], // D6
            $responses['Q13'], // D7
            $responses['Q15'], // D8
            $responses['Q2'],  // E1
            $responses['Q4'],  // E2
            $responses['Q6'],  // E3
            $responses['Q8'],  // E4
            $responses['Q10'], // E5
            $responses['Q12'], // E6
            $responses['Q14'], // E7
            $responses['Q16'], // E8
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
                // Overwrite with our own calculation for consistency
                $totalScore = $exhaustionScore + $disengagementScore;
                $exhaustionScore = $exhaustionScore;
                $disengagementScore = $disengagementScore;
            }
        } catch (\Exception $e) {
            $errorMsg = 'Prediction error: ' . $e->getMessage();
        }

        $overallRisk = $predictedLabel ? strtolower($predictedLabel) : 'unknown';

        return view('assessment.result', compact(
            'responses', 'original_responses', 'student_id', 'name', 'year_level',
            'totalScore', 'predictedLabel', 'confidence', 'labels',
            'exhaustionScore', 'disengagementScore', 'exhaustionItems', 'disengagementItems', 'errorMsg', 'overallRisk'
        ));
    }

    public function results($id)
    {
        $assessment = Assessment::findOrFail($id);
        return view('assessment.results', compact('assessment'));
    }
}