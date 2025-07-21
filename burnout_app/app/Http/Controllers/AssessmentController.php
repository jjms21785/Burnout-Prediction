<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\Http;

class AssessmentController extends Controller
{
    // Remove BurnoutCalculatorService dependency

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

        return view('assessment.index', compact('questions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'answers' => 'required|array|size:22',
            'answers.*' => 'required|integer|min:0|max:6'
        ]);

        $assessment = Assessment::create([
            'answers' => json_encode($validated['answers']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Prepare data for Flask API
        $payload = [];
        foreach ($validated['answers'] as $i => $answer) {
            $payload['Q' . ($i + 1)] = $answer;
        }

        // Call Flask ML API
        $response = Http::post('http://127.0.0.1:5000/predict', $payload);
        $prediction = strtolower($response->json('prediction'));

        // Store only the ML model's output (risk level)
        $assessment->update([
            'overall_risk' => $prediction,
            'confidence' => null,
            'ee_score' => null,
            'dp_score' => null,
            'pa_score' => null
        ]);

        return redirect()->route('assessment.results', $assessment->id);
    }

    public function results($id)
    {
        $assessment = Assessment::findOrFail($id);
        
        return view('assessment.results', compact('assessment'));
    }
}