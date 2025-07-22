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
        $questions = [
            'I feel emotionally drained from my work/studies',
            'I have trouble sleeping because of my studies',
            'Working with people all day is really a strain for me',
            'I feel burned out from my studies',
            'I feel frustrated by my studies',
            'I feel I\'m working too hard in my studies',
            'I don\'t really care what happens to some students',
            'Working directly with people puts too much stress on me',
            'I feel like I\'m at the end of my rope',
            'I feel very energetic',
            'I feel exhilarated after working closely with my recipients',
            'I have accomplished many worthwhile things in this job',
            'I can easily understand how my recipients feel about things',
            'I deal very effectively with the problems of my recipients',
            'I feel I\'m positively influencing other people\'s lives through my work',
            'I feel very energetic',
            'I can easily create a relaxed atmosphere with my recipients',
            'I feel exhilarated after working closely with my recipients',
            'I have accomplished many worthwhile things in this job',
            'In my work, I deal with emotional problems very calmly',
            'I feel recipients blame me for some of their problems',
            'I worry that this job is hardening me emotionally'
        ];
        $programs = [
            'Computer Science',
            'Information Technology',
            'Other'
        ];
        $year_levels = [
            '1st Year', '2nd Year', '3rd Year', '4th Year'
        ];
        $genders = ['Male', 'Female', 'Other'];
        return view('assessment.index', compact('questions', 'programs', 'year_levels', 'genders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9\-]+$/'],
            'name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'age' => 'required|integer|min:10|max:100',
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'program' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z ]+$/'],
            'year_level' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9 ]+$/'],
            'answers' => 'required|array|size:16',
            'answers.*' => 'required|integer|min:0|max:3'
        ], [
            'student_id.regex' => 'Student ID may only contain letters, numbers, and dashes.',
            'name.regex' => 'Name may only contain letters and spaces.',
            'program.regex' => 'Program may only contain letters and spaces.',
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
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'program' => $validated['program'],
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
        $age = (int) $request->input('age');
        $gender = $request->input('gender');
        $program = $request->input('program');

        // 2. Reverse scoring
        $reverseItems = ['Q1', 'Q4', 'Q7', 'Q8', 'Q11', 'Q13', 'Q15', 'Q16'];
        foreach ($reverseItems as $item) {
            $responses[$item] = 5 - $responses[$item];
        }

        // 3. Score breakdown
        $exhaustionItems = ['Q2', 'Q5', 'Q6', 'Q10', 'Q12', 'Q14'];
        $disengagementItems = ['Q1', 'Q3', 'Q4', 'Q7', 'Q8', 'Q9', 'Q11', 'Q13', 'Q15', 'Q16'];
        $exhaustionScore = array_sum(array_intersect_key($responses, array_flip($exhaustionItems)));
        $disengagementScore = array_sum(array_intersect_key($responses, array_flip($disengagementItems)));

        // 4. Total score
        $totalScore = array_sum($responses);

        // 5. Prepare input for model
        $modelInput = array_values($responses);
        $modelInput[] = $age;
        $genderMap = ['Male' => 0, 'Female' => 1];
        $programMap = [
            'BSA' => 0, 'BSBA' => 1, 'BSENT' => 2, 'BSHM' => 3, 'BEED' => 4,
            'BSED-ENG' => 5, 'BSED-FIL' => 6, 'BSED-MATH' => 7, 'BA-PSYCH' => 8,
            'BSCS' => 9, 'BSIT' => 10, 'BSECE' => 11, 'BSN' => 12
        ];
        $modelInput[] = $genderMap[$gender] ?? 0;
        $modelInput[] = $programMap[$program] ?? 0;

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
            'responses', 'age', 'gender', 'program',
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