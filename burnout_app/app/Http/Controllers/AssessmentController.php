<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResultController;

class AssessmentController extends Controller
{
    protected $questionController;

    public function __construct(QuestionController $questionController)
    {
        $this->questionController = $questionController;
    }

    public function index()
    {
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
        
        $year_levels = ['First', 'Second', 'Third', 'Fourth'];
        $genders = ['Male', 'Female'];
        
        return view('assessment.index', compact('questions', 'programs', 'year_levels', 'genders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'age' => ['required', 'integer', 'min:10', 'max:100'],
            'gender' => ['required', 'string', 'max:255'],
            'program' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],
            'answers' => 'required|array|size:30',
            'answers.*' => 'required|integer|min:0|max:4'
        ], [
            'first_name.regex' => 'First name may only contain letters and spaces.',
            'last_name.regex' => 'Last name may only contain letters and spaces.',
            'year_level.regex' => 'Year level may only contain letters, numbers, and spaces.'
        ]);

        $firstName = trim($validated['first_name'] ?? '');
        $lastName = trim($validated['last_name'] ?? '');
        
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
            'sex' => $validated['gender'],
            'college' => $validated['program'],
            'year' => $validated['year_level'],
            'answers' => json_encode($validated['answers']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $responses = [];
        foreach ($validated['answers'] as $i => $answer) {
            $responses['Q' . ($i + 1)] = (int) $answer;
        }

        $exhaustionItems = ['Q16', 'Q17', 'Q20', 'Q21', 'Q23', 'Q25', 'Q28', 'Q29'];
        $disengagementItems = ['Q15', 'Q18', 'Q19', 'Q22', 'Q24', 'Q26', 'Q27', 'Q30'];
        
        $exhaustionScore = array_sum(array_intersect_key($responses, array_flip($exhaustionItems)));
        $disengagementScore = array_sum(array_intersect_key($responses, array_flip($disengagementItems)));

        $allAnswers = [];
        for ($i = 1; $i <= 30; $i++) {
            $allAnswers[] = $responses['Q' . $i] ?? 0;
        }

        $processedData = null;
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('http://127.0.0.1:5000/predict', [
                'all_answers' => $allAnswers
            ]);
            
            if ($response->failed()) {
                throw new \Exception('Flask service unavailable');
            }
            
            $json = $response->json();
            $processedData = ResultController::processPythonResponse($json);
            
            $exhaustionCategory = $processedData['exhaustion_category'] ?? null;
            $disengagementCategory = $processedData['disengagement_category'] ?? null;
            $interpretations = $processedData['interpretations'] ?? null;
            $recommendations = $processedData['recommendations'] ?? null;
            $barGraph = $processedData['bar_graph'] ?? null;
            $dataAvailable = $processedData['data_available'] ?? false;
            
            $exhaustion = $exhaustionScore;
            $disengagement = $disengagementScore;
            if ($barGraph) {
                if (isset($barGraph['Exhaustion'])) {
                    $exhaustionAvg = ($barGraph['Exhaustion'] / 100) * 4;
                    $exhaustion = round($exhaustionAvg * 8);
                }
                if (isset($barGraph['Disengagement'])) {
                    $disengagementAvg = ($barGraph['Disengagement'] / 100) * 4;
                    $disengagement = round($disengagementAvg * 8);
                }
            }
            
        } catch (\Exception $e) {
            $exhaustionAverage = count($exhaustionItems) > 0 ? $exhaustionScore / count($exhaustionItems) : 0;
            $disengagementAverage = count($disengagementItems) > 0 ? $disengagementScore / count($disengagementItems) : 0;
            
            $exhaustion = $exhaustionScore;
            $disengagement = $disengagementScore;
            $exhaustionCategory = $exhaustionAverage >= 2.25 ? 'High' : 'Low';
            $disengagementCategory = $disengagementAverage >= 2.10 ? 'High' : 'Low';
            $interpretations = null;
            $recommendations = null;
            $barGraph = null;
            $dataAvailable = false;
        }

        $overallRisk = null;
        if ($processedData && isset($processedData['predicted_category'])) {
            $categoryNum = (int)$processedData['predicted_category'];
            if ($categoryNum >= 0 && $categoryNum <= 3) {
                $overallRisk = (string)$categoryNum;
            }
        }
        
        $assessment->update([
            'Burnout_Category' => $overallRisk,
            'Exhaustion' => $exhaustion,
            'Disengagement' => $disengagement,
        ]);
        
        $assessment->answers = json_encode([
            'responses' => $validated['answers'],
            'interpretations' => $interpretations,
            'recommendations' => $recommendations,
            'bar_graph' => $barGraph ?? null,
            'python_response' => $json ?? null,
            'data_available' => $dataAvailable ?? false
        ]);
        $assessment->save();

        return redirect()->route('assessment.results', $assessment->id);
    }

    public function calculateBurnout(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'age' => ['required', 'integer', 'min:10', 'max:100'],
            'gender' => ['required', 'string', 'max:255'],
            'program' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],
            'answers' => 'required|array|size:30',
            'answers.*' => 'required|integer|min:0|max:4'
        ], [
            'first_name.regex' => 'First name may only contain letters and spaces.',
            'last_name.regex' => 'Last name may only contain letters and spaces.',
            'year_level.regex' => 'Year level may only contain letters, numbers, and spaces.'
        ]);

        $answers = $validated['answers'];
        $responses = [];
        $original_responses = [];
        
        for ($i = 0; $i < 30; $i++) {
            $responses["Q" . ($i + 1)] = (int) ($answers[$i] ?? 0);
            $original_responses["Q" . ($i + 1)] = (int) ($answers[$i] ?? 0);
        }

        $firstName = trim($validated['first_name'] ?? '');
        $lastName = trim($validated['last_name'] ?? '');
        
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

        $exhaustionItems = ['Q16', 'Q17', 'Q20', 'Q21', 'Q23', 'Q25', 'Q28', 'Q29'];
        $disengagementItems = ['Q15', 'Q18', 'Q19', 'Q22', 'Q24', 'Q26', 'Q27', 'Q30'];
        $exhaustionScore = array_sum(array_intersect_key($responses, array_flip($exhaustionItems)));
        $disengagementScore = array_sum(array_intersect_key($responses, array_flip($disengagementItems)));

        $exhaustionAverage = count($exhaustionItems) > 0 ? $exhaustionScore / count($exhaustionItems) : 0;
        $disengagementAverage = count($disengagementItems) > 0 ? $disengagementScore / count($disengagementItems) : 0;
        $exhaustionCategory = $exhaustionAverage >= 2.25 ? 'High' : 'Low';
        $disengagementCategory = $disengagementAverage >= 2.10 ? 'High' : 'Low';
        $totalScore = $exhaustionScore + $disengagementScore;

        $allAnswers = [];
        for ($i = 0; $i < 30; $i++) {
            $allAnswers[] = (int) ($validated['answers'][$i] ?? 0);
        }
        
        if (count($allAnswers) !== 30) {
            Log::error('Invalid answers count', [
                'count' => count($allAnswers),
                'answers' => $validated['answers'] ?? null
            ]);
            return back()->withErrors(['answers' => 'Invalid number of answers. Please ensure all 30 questions are answered.'])->withInput();
        }

        $apiUrl = 'http://127.0.0.1:5000/predict';
        $errorMsg = null;
        $predictedLabel = null;
        $interpretations = null;
        $recommendations = null;
        $barGraph = null;
        $dataAvailable = false;
        $pythonResponse = null;
        $processedData = null;
        
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($apiUrl, [
                'all_answers' => $allAnswers
            ]);
            
            if ($response->failed()) {
                $errorMsg = 'Prediction service unavailable. Response status: ' . $response->status();
                Log::error('Flask API failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                if ($response->status() === 400) {
                    $errorMsg = 'Invalid data format sent to prediction service. Please try again.';
                }
            } else {
                $pythonResponse = $response->json();
                
                if (!is_array($pythonResponse) || !isset($pythonResponse['PredictedResult'])) {
                    Log::error('Invalid Python response structure', [
                        'response' => $pythonResponse
                    ]);
                    throw new \Exception('Invalid response format from prediction service');
                }
                
                $processedData = ResultController::processPythonResponse($pythonResponse);
                
                $predictedLabel = $processedData['predicted_label'] ?? null;
                $exhaustionCategory = $processedData['exhaustion_category'] ?? $exhaustionCategory;
                $disengagementCategory = $processedData['disengagement_category'] ?? $disengagementCategory;
                $interpretations = $processedData['interpretations'] ?? null;
                $recommendations = $processedData['recommendations'] ?? null;
                $barGraph = $processedData['bar_graph'] ?? null;
                $dataAvailable = $processedData['data_available'] ?? false;
                
                if ($barGraph) {
                    if (isset($barGraph['Exhaustion'])) {
                        $exhaustionAvg = ($barGraph['Exhaustion'] / 100) * 4;
                        $exhaustionScore = round($exhaustionAvg * 8);
                    }
                    if (isset($barGraph['Disengagement'])) {
                        $disengagementAvg = ($barGraph['Disengagement'] / 100) * 4;
                        $disengagementScore = round($disengagementAvg * 8);
                    }
                }
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $errorMsg = 'Could not connect to prediction service. Please ensure the Flask API is running on http://127.0.0.1:5000';
            Log::error('Flask connection error', ['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            $errorMsg = 'Prediction error: ' . $e->getMessage();
            Log::error('Flask API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $overallRisk = null;
        if ($processedData && isset($processedData['predicted_category'])) {
            $categoryNum = (int)$processedData['predicted_category'];
            if ($categoryNum >= 0 && $categoryNum <= 3) {
                $overallRisk = (string)$categoryNum;
            }
        }

        $assessment = Assessment::create([
            'name' => $name,
            'age' => $age,
            'sex' => $gender,
            'college' => $program,
            'year' => $year_level,
            'answers' => json_encode([
                'responses' => $original_responses,
                'interpretations' => $interpretations,
                'recommendations' => $recommendations
            ]),
            'Burnout_Category' => $overallRisk,
            'Exhaustion' => $exhaustionScore,
            'Disengagement' => $disengagementScore,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $resultData = ResultController::processResultForView(
            $dataAvailable,
            $exhaustionCategory,
            $disengagementCategory,
            $barGraph,
            $errorMsg
        );

        return view('assessment.result', compact(
            'responses', 'original_responses', 'name', 'age', 'gender', 'program', 'year_level',
            'totalScore', 'predictedLabel', 'exhaustionScore', 'disengagementScore', 'exhaustionItems', 'disengagementItems', 'errorMsg', 'overallRisk',
            'exhaustionAverage', 'disengagementAverage', 'exhaustionCategory', 'disengagementCategory',
            'interpretations', 'recommendations', 'barGraph', 'dataAvailable'
        ))->with($resultData);
    }

    private function extractResponsesFromAssessment($assessment)
    {
        $original_responses = $assessment->raw_answers ?? [];
        $responses = [];
        
        if (!empty($original_responses) && is_array($original_responses)) {
            if (isset($original_responses['Q1'])) {
                $responses = $original_responses;
            } else {
                foreach ($original_responses as $i => $answer) {
                    if ($answer !== null && $answer !== '') {
                        $responses['Q' . ($i + 1)] = (int) $answer;
                    }
                }
            }
        }
        
        if (empty($responses)) {
            $answersData = $assessment->answers;
            if (is_string($answersData)) {
                $answersData = json_decode($answersData, true) ?? [];
            }
            if (isset($answersData['responses']) && is_array($answersData['responses'])) {
                $responses = $answersData['responses'];
            }
        }
        
        return ['responses' => $responses, 'original_responses' => $original_responses];
    }

    public function results($id)
    {
        $assessment = Assessment::findOrFail($id);
        
        $responseData = $this->extractResponsesFromAssessment($assessment);
        $responses = $responseData['responses'];
        $original_responses = $responseData['original_responses'];
        
        $answersData = $assessment->answers;
        if (is_string($answersData)) {
            $answersData = json_decode($answersData, true) ?? [];
        }
        $pythonResponse = $answersData['python_response'] ?? null;
        
        $exhaustionItems = ['Q16', 'Q17', 'Q20', 'Q21', 'Q23', 'Q25', 'Q28', 'Q29'];
        $disengagementItems = ['Q15', 'Q18', 'Q19', 'Q22', 'Q24', 'Q26', 'Q27', 'Q30'];
        $exhaustionScore = $assessment->Exhaustion ?? null;
        $disengagementScore = $assessment->Disengagement ?? null;
        
        if ($exhaustionScore === null && !empty($responses)) {
            $exhaustionScore = array_sum(array_intersect_key($responses, array_flip($exhaustionItems)));
        }
        if ($disengagementScore === null && !empty($responses)) {
            $disengagementScore = array_sum(array_intersect_key($responses, array_flip($disengagementItems)));
        }
        
        $exhaustionAverage = count($exhaustionItems) > 0 ? ($exhaustionScore ?? 0) / count($exhaustionItems) : 0;
        $disengagementAverage = count($disengagementItems) > 0 ? ($disengagementScore ?? 0) / count($disengagementItems) : 0;
        $exhaustionCategory = $exhaustionAverage >= 2.25 ? 'High' : 'Low';
        $disengagementCategory = $disengagementAverage >= 2.10 ? 'High' : 'Low';
        $totalScore = ($exhaustionScore ?? 0) + ($disengagementScore ?? 0);
        
        $interpretations = null;
        $recommendations = null;
        $barGraph = null;
        $dataAvailable = false;
        $predictedLabel = null;
        
        if ($pythonResponse) {
            $processedData = ResultController::processPythonResponse($pythonResponse);
            $predictedLabel = $processedData['predicted_label'] ?? null;
            $exhaustionCategory = $processedData['exhaustion_category'] ?? $exhaustionCategory;
            $disengagementCategory = $processedData['disengagement_category'] ?? $disengagementCategory;
            $interpretations = $processedData['interpretations'] ?? null;
            $recommendations = $processedData['recommendations'] ?? null;
            $barGraph = $processedData['bar_graph'] ?? null;
            $dataAvailable = $processedData['data_available'] ?? false;
            
            if ($barGraph) {
                if (isset($barGraph['Exhaustion'])) {
                    $exhaustionAvg = ($barGraph['Exhaustion'] / 100) * 4;
                    $exhaustionScore = round($exhaustionAvg * 8);
                }
                if (isset($barGraph['Disengagement'])) {
                    $disengagementAvg = ($barGraph['Disengagement'] / 100) * 4;
                    $disengagementScore = round($disengagementAvg * 8);
                }
            }
        } else {
            $interpretations = $assessment->interpretations ?? $answersData['interpretations'] ?? null;
            $recommendations = $assessment->recommendations ?? $answersData['recommendations'] ?? null;
            $barGraph = $answersData['bar_graph'] ?? null;
        }

        if (!$interpretations) {
            $interpretations = ResultController::generateBasicInterpretations($exhaustionCategory, $disengagementCategory, $exhaustionAverage, $disengagementAverage);
        }
        if (!$recommendations) {
            $recommendations = ResultController::generateBasicRecommendations($exhaustionCategory, $disengagementCategory);
        }
        if (!$dataAvailable && ($exhaustionScore !== null || $disengagementScore !== null || !empty($responses))) {
            $dataAvailable = true;
        }
        
        if (!$predictedLabel) {
            $predictedLabel = ucfirst($assessment->Burnout_Category ?? 'Unknown');
        }
        
        $name = $assessment->name ?? 'Unavailable';
        $age = $assessment->age ?? null;
        $gender = $assessment->sex ?? 'Unavailable';
        $program = $assessment->college ?? 'Unavailable';
        $year_level = $assessment->year ?? 'Unavailable';
        
        $resultData = ResultController::processResultForView($dataAvailable, $exhaustionCategory, $disengagementCategory, $barGraph, null);
        
        return view('assessment.result', compact(
            'assessment', 'responses', 'original_responses', 
            'name', 'age', 'gender', 'program', 'year_level',
            'exhaustionScore', 'disengagementScore', 'totalScore',
            'exhaustionAverage', 'disengagementAverage', 'exhaustionCategory', 'disengagementCategory',
            'interpretations', 'recommendations', 'predictedLabel', 'barGraph', 'dataAvailable'
        ))->with($resultData);
    }

    public function showResultError()
    {
        return redirect()->route('assessment.index')->with('error', 'Please complete the assessment form first.');
    }
}
