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
            'I always find new and interesting aspects in my studies.',
            'It happens more and more often that I talk about my studies in a negative way.',
            'Lately, I tend to think less about my academic tasks and do them almost mechanically.',
            'I find my studies to be a positive challenge.',
            'Over time, one can become disconnected from this type of study.',
            'Sometimes I feel sickened by my studies.',
            'This is the only field of study that I can imagine myself doing.',
            'I feel more and more engaged in my studies.',
            'There are days when I feel tired before I arrive in class or start studying.',
            'After a class or after studying, I tend to need more time than in the past in order to relax and feel better.',
            'I can tolerate the pressure of my studies very well.',
            'While studying, I usually feel emotionally drained.',
            'After a class or after studying, I have enough energy for my leisure activities.',
            'After a class or after studying, I usually feel worn out and weary.',
            'I can usually manage my study-related workload well.',
            'When I study, I usually feel energized.'
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
        // 1. Collect responses
        $responses = [];
        for ($i = 1; $i <= 16; $i++) {
            $responses["Q$i"] = (int) $request->input("Q$i");
        }
        $original_responses = $responses;
        $student_id = $request->input('student_id');
        $name = $request->input('name');
        $year_level = $request->input('year_level');

        // 2. Reverse scoring
        // Positively worded items (to be reverse scored):
        // Q1: I always find new and interesting aspects in my studies.
        // Q4: I find my studies to be a positive challenge.
        // Q7: This is the only field of study that I can imagine myself doing.
        // Q8: I feel more and more engaged in my studies.
        // Q11: I can tolerate the pressure of my studies very well.
        // Q13: After a class or after studying, I have enough energy for my leisure activities.
        // Q15: I can usually manage my study-related workload well.
        // Q16: When I study, I usually feel energized.
        $reverseItems = ['Q1', 'Q4', 'Q7', 'Q8', 'Q11', 'Q13', 'Q15', 'Q16'];
        foreach ($reverseItems as $item) {
            $responses[$item] = 5 - $responses[$item];
        }

        // 3. Score breakdown
        // Corrected OLBI-S mapping:
        // Exhaustion items:
        // Q9: There are days when I feel tired before I arrive in class or start studying
        // Q10: After a class or after studying, I tend to need more time than in the past in order to relax and feel better.
        // Q11: I can tolerate the pressure of my studies very well
        // Q12: While studying, I usually feel emotionally drained.
        // Q13: After a class or after studying, I have enough energy for my leisure activities.
        // Q14: After a class or after studying, I usually feel worn out and weary.
        // Q15: I can usually manage my study-related workload well.
        // Q16: When I study, I usually feel energized
        $exhaustionItems = ['Q9', 'Q10', 'Q11', 'Q12', 'Q13', 'Q14', 'Q15', 'Q16'];
        // Disengagement items: all others
        $disengagementItems = ['Q1', 'Q2', 'Q3', 'Q4', 'Q5', 'Q6', 'Q7', 'Q8'];
        $exhaustionScore = array_sum(array_intersect_key($responses, array_flip($exhaustionItems)));
        $disengagementScore = array_sum(array_intersect_key($responses, array_flip($disengagementItems)));

        // 4. Total score
        $totalScore = array_sum($responses);

        // 5. Prepare input for model
        $modelInput = array_values($responses);

        // 6. Call Python API for prediction
        $apiUrl = 'http://127.0.0.1:5000/predict';
        $labels = ['Low', 'Moderate', 'High'];
        $errorMsg = null;
        $predictedLabel = null;
        $confidence = null;
        $modelAccuracy = null;
        try {
            $response = \Illuminate\Support\Facades\Http::post($apiUrl, ['input' => $modelInput]);
            if ($response->failed()) {
                $errorMsg = 'Prediction service unavailable.';
            } else {
                $result = $response->json();
                $predictedLabel = $result['label'] ?? null;
                $confidence = $result['confidence'] ?? null;
                $modelAccuracy = $result['accuracy'] ?? null;
                $totalScore = $result['total_score'] ?? null;
                $exhaustionScore = $result['exhaustion'] ?? null;
                $disengagementScore = $result['disengagement'] ?? null;
            }
        } catch (\Exception $e) {
            $errorMsg = 'Prediction error: ' . $e->getMessage();
        }

        $overallRisk = $predictedLabel ? strtolower($predictedLabel) : 'unknown';

        return view('assessment.result', compact(
            'responses', 'original_responses', 'student_id', 'name', 'year_level',
            'totalScore', 'predictedLabel', 'confidence', 'labels',
            'exhaustionScore', 'disengagementScore', 'exhaustionItems', 'disengagementItems', 'modelAccuracy', 'errorMsg', 'overallRisk'
        ));
    }

    public function results($id)
    {
        $assessment = Assessment::findOrFail($id);
        return view('assessment.results', compact('assessment'));
    }
}