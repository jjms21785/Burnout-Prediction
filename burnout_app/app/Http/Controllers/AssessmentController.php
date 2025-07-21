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

        $assessment->update([
            'overall_risk' => $prediction,
            'confidence' => $confidence,
            'exhaustion_score' => $exhaustion,
            'disengagement_score' => $disengagement
        ]);

        return redirect()->route('assessment.results', $assessment->id);
    }

    public function results($id)
    {
        $assessment = Assessment::findOrFail($id);
        return view('assessment.results', compact('assessment'));
    }
}