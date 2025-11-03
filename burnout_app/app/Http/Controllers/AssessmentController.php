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
            'First', 'Second', 'Third', 'Fourth'
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
            'gender' => ['required', 'string', 'max:255'], // Increased max for "Others" custom values
            'program' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'], // Increased max for "Others" custom values
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

        // Create assessment initially without interpretation data
        // Map form fields to database column names
        $assessment = Assessment::create([
            'name' => $validated['name'],
            'age' => $validated['age'],
            'sex' => $validated['gender'], // Map gender -> sex
            'college' => $validated['program'], // Map program -> college
            'year' => $validated['year_level'], // Map year_level -> year
            'answers' => json_encode($validated['answers']), // Store as JSON string (no cast)
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Collect responses
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

        // Prepare all 30 answers for Flask (0-indexed array)
        $allAnswers = [];
        for ($i = 1; $i <= 30; $i++) {
            $allAnswers[] = $responses['Q' . $i] ?? 0;
        }

        // Send all 30 answers to Flask for prediction and interpretation
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
            
            // Process Python response using ResultController
            $processedData = ResultController::processPythonResponse($json);
            
            // Extract processed data
            $prediction = strtolower($processedData['predicted_label'] ?? '');
            $exhaustionCategory = $processedData['exhaustion_category'] ?? null;
            $disengagementCategory = $processedData['disengagement_category'] ?? null;
            $interpretations = $processedData['interpretations'] ?? null;
            $recommendations = $processedData['recommendations'] ?? null;
            $barGraph = $processedData['bar_graph'] ?? null;
            $dataAvailable = $processedData['data_available'] ?? false;
            
            // Calculate exhaustion and disengagement scores from bar graph percentages
            // Bar graph has percentages, we need to reverse calculate scores
            // But always fallback to calculated scores if bar graph values are missing
            $exhaustion = $exhaustionScore; // Default to calculated score
            $disengagement = $disengagementScore; // Default to calculated score
            if ($barGraph) {
                // Reverse calculate: percentage * 4 / 100 = average
                // Then average * 8 = total score
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
            // Fallback if Flask is unavailable - calculate categories locally
            $exhaustionAverage = count($exhaustionItems) > 0 ? $exhaustionScore / count($exhaustionItems) : 0;
            $disengagementAverage = count($disengagementItems) > 0 ? $disengagementScore / count($disengagementItems) : 0;
            
            $prediction = 'unknown';
            $confidence = null;
            $exhaustion = $exhaustionScore;
            $disengagement = $disengagementScore;
            $exhaustionCategory = $exhaustionAverage >= 2.25 ? 'High' : 'Low';
            $disengagementCategory = $disengagementAverage >= 2.10 ? 'High' : 'Low';
            $interpretationData = null;
            $interpretations = null;
            $recommendations = null;
            $barGraph = null;
            $dataAvailable = false;
        }

        // Map Python prediction labels to database enum values
        $overallRisk = 'unknown';
        if ($prediction) {
            $label = strtolower($prediction);
            // Map Python labels: Non-Burnout -> low, Disengaged/Exhausted -> moderate, BURNOUT -> high
            if (in_array($label, ['low', 'moderate', 'high', 'non-burnout', 'non burnout'])) {
                if (in_array($label, ['non-burnout', 'non burnout'])) {
                    $overallRisk = 'low';
                } else {
                    $overallRisk = $label;
                }
            } elseif (in_array($label, ['disengaged', 'exhausted'])) {
                $overallRisk = 'moderate';
            } elseif ($label === 'burnout') {
                $overallRisk = 'high';
            }
        }
        
        // Store interpretation data - map to database column names
        $assessment->update([
            'Burnout_Category' => $overallRisk, // Map overall_risk -> Burnout_Category
            'Exhaustion' => $exhaustion, // Map exhaustion_score -> Exhaustion
            'Disengagement' => $disengagement, // Map disengagement_score -> Disengagement
            // Store confidence if your database has this column
            // 'confidence' => is_array($confidence) ? max($confidence) : ($confidence ?: null),
        ]);
        
        // Store interpretation data in the answers field
        // Update answers to include both responses, interpretations, and bar graph data
        // Manually encode to JSON since $casts is removed
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
        // Validate the request
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'last_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z ]*$/'],
            'age' => ['required', 'integer', 'min:10', 'max:100'],
            'gender' => ['required', 'string', 'max:255'], // Increased max for "Others" custom values
            'program' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'], // Increased max for "Others" custom values
            'answers' => 'required|array|size:30',
            'answers.*' => 'required|integer|min:0|max:4'
        ], [
            'first_name.regex' => 'First name may only contain letters and spaces.',
            'last_name.regex' => 'Last name may only contain letters and spaces.',
            'year_level.regex' => 'Year level may only contain letters, numbers, and spaces.'
        ]);

        // Collect responses from answers array (all 30 questions)
        $answers = $validated['answers'];
        $responses = [];
        $original_responses = [];
        
        for ($i = 0; $i < 30; $i++) {
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

        // Prepare all 30 answers for Flask (0-indexed array)
        // Use validated answers directly to ensure correct order
        $allAnswers = [];
        for ($i = 0; $i < 30; $i++) {
            $allAnswers[] = (int) ($validated['answers'][$i] ?? 0);
        }
        
        // Validate we have exactly 30 answers
        if (count($allAnswers) !== 30) {
            Log::error('Invalid answers count', [
                'count' => count($allAnswers),
                'answers' => $validated['answers'] ?? null
            ]);
            return back()->withErrors(['answers' => 'Invalid number of answers. Please ensure all 30 questions are answered.'])->withInput();
        }

        // Python API prediction with all interpretations
        $apiUrl = 'http://127.0.0.1:5000/predict';
        $labels = ['Low', 'Moderate', 'High'];
        $errorMsg = null;
        $predictedLabel = null;
        $confidence = null;
        $interpretations = null;
        $recommendations = null;
        $barGraph = null;
        $dataAvailable = false;
        $pythonResponse = null;
        
        try {
            // Log the request being sent to Flask for debugging
            Log::info('Sending request to Flask API', [
                'url' => $apiUrl,
                'answers_count' => count($allAnswers),
                'first_5_answers' => array_slice($allAnswers, 0, 5)
            ]);
            
            $response = \Illuminate\Support\Facades\Http::timeout(10)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($apiUrl, [
                'all_answers' => $allAnswers
            ]);
            
            // Log the response for debugging
            Log::info('Flask API response', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'body_preview' => substr($response->body(), 0, 200)
            ]);
            
            if ($response->failed()) {
                $errorMsg = 'Prediction service unavailable. Response status: ' . $response->status();
                $errorBody = $response->body();
                Log::error('Flask API failed', [
                    'status' => $response->status(),
                    'body' => $errorBody
                ]);
                
                // If it's a 400 error, it might be a data format issue
                if ($response->status() === 400) {
                    $errorMsg = 'Invalid data format sent to prediction service. Please try again.';
                }
            } else {
                $pythonResponse = $response->json();
                
                // Validate Python response structure
                if (!is_array($pythonResponse) || !isset($pythonResponse['PredictedResult'])) {
                    Log::error('Invalid Python response structure', [
                        'response' => $pythonResponse
                    ]);
                    throw new \Exception('Invalid response format from prediction service');
                }
                
                // Process Python response using ResultController (same as store method)
                $processedData = ResultController::processPythonResponse($pythonResponse);
                
                // Extract processed data
                $predictedLabel = $processedData['predicted_label'] ?? null;
                $exhaustionCategory = $processedData['exhaustion_category'] ?? $exhaustionCategory;
                $disengagementCategory = $processedData['disengagement_category'] ?? $disengagementCategory;
                $interpretations = $processedData['interpretations'] ?? null;
                $recommendations = $processedData['recommendations'] ?? null;
                $barGraph = $processedData['bar_graph'] ?? null;
                $dataAvailable = $processedData['data_available'] ?? false;
                
                // Calculate exhaustion and disengagement scores from bar graph percentages if available
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
            // Fallback: Calculate categories locally when Flask is unavailable
            if (!$dataAvailable) {
                // Categories already calculated above, just need to set dataAvailable flag
                // but keep interpretations/recommendations as null since we don't have Python response
            }
        } catch (\Exception $e) {
            $errorMsg = 'Prediction error: ' . $e->getMessage();
            Log::error('Flask API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Fallback: Calculate categories locally when Flask is unavailable
            if (!$dataAvailable) {
                // Categories already calculated above, just need to set dataAvailable flag
                // but keep interpretations/recommendations as null since we don't have Python response
            }
        }

        // Map prediction labels to database enum values
        $overallRisk = 'unknown';
        if ($predictedLabel) {
            $label = strtolower($predictedLabel);
            // Map Python labels: Non-Burnout -> low, Disengaged/Exhausted -> moderate, BURNOUT -> high
            if (in_array($label, ['low', 'moderate', 'high', 'non-burnout', 'non burnout'])) {
                if (in_array($label, ['non-burnout', 'non burnout'])) {
                    $overallRisk = 'low';
                } else {
                    $overallRisk = $label;
                }
            } elseif (in_array($label, ['disengaged', 'exhausted'])) {
                $overallRisk = 'moderate';
            } elseif ($label === 'burnout') {
                $overallRisk = 'high';
            }
        }

        // Save assessment to database with interpretation data
        // Map form fields to database column names
        $assessment = Assessment::create([
            'name' => $name,
            'age' => $age,
            'sex' => $gender, // Map gender -> sex
            'college' => $program, // Map program -> college
            'year' => $year_level, // Map year_level -> year
            'answers' => json_encode([
                'responses' => $original_responses,
                'interpretations' => $interpretations,
                'recommendations' => $recommendations
            ]),
            'Burnout_Category' => $overallRisk, // Map overall_risk -> Burnout_Category
            'Exhaustion' => $exhaustionScore, // Map exhaustion_score -> Exhaustion
            'Disengagement' => $disengagementScore, // Map disengagement_score -> Disengagement
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Process result data for view using ResultController
        $resultData = ResultController::processResultForView(
            $dataAvailable,
            $exhaustionCategory,
            $disengagementCategory,
            $barGraph,
            $errorMsg
        );

        return view('assessment.result', compact(
            'responses', 'original_responses', 'name', 'age', 'gender', 'program', 'year_level',
            'totalScore', 'predictedLabel', 'confidence', 'labels',
            'exhaustionScore', 'disengagementScore', 'exhaustionItems', 'disengagementItems', 'errorMsg', 'overallRisk',
            'exhaustionAverage', 'disengagementAverage', 'exhaustionCategory', 'disengagementCategory',
            'interpretations', 'recommendations', 'barGraph', 'dataAvailable'
        ))->with($resultData);
    }

    /**
     * Extract responses from assessment (used by both calculateBurnout and results)
     */
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
        
        // Try answersData if still empty
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
        
        // Extract responses same way calculateBurnout does
        $responseData = $this->extractResponsesFromAssessment($assessment);
        $responses = $responseData['responses'];
        $original_responses = $responseData['original_responses'];
        
        // Get stored Python response (same as calculateBurnout would have)
        $answersData = $assessment->answers;
        if (is_string($answersData)) {
            $answersData = json_decode($answersData, true) ?? [];
        }
        $pythonResponse = $answersData['python_response'] ?? null;
        
        // Reuse exact same calculation logic as calculateBurnout
        $exhaustionItems = ['Q16', 'Q17', 'Q20', 'Q21', 'Q23', 'Q25', 'Q28', 'Q29'];
        $disengagementItems = ['Q15', 'Q18', 'Q19', 'Q22', 'Q24', 'Q26', 'Q27', 'Q30'];
        $exhaustionScore = $assessment->Exhaustion ?? $assessment->exhaustion_score ?? null;
        $disengagementScore = $assessment->Disengagement ?? $assessment->disengagement_score ?? null;
        
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
        
        // Process Python response same way calculateBurnout does
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
            
            // Recalculate scores from bar graph if available (same as calculateBurnout)
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
            // Get stored interpretations/recommendations
            $interpretations = $assessment->interpretations ?? $answersData['interpretations'] ?? null;
            $recommendations = $assessment->recommendations ?? $answersData['recommendations'] ?? null;
            $barGraph = $answersData['bar_graph'] ?? null;
        }

        // Removed special synthesis of bar graph when viewing from View Report page
        
        // Generate basic if missing (same fallback as calculateBurnout)
        if (!$interpretations) {
            $interpretations = ResultController::generateBasicInterpretations($exhaustionCategory, $disengagementCategory, $exhaustionAverage, $disengagementAverage);
        }
        if (!$recommendations) {
            $recommendations = ResultController::generateBasicRecommendations($exhaustionCategory, $disengagementCategory);
        }
        if (!$dataAvailable && ($exhaustionScore !== null || $disengagementScore !== null || !empty($responses))) {
            $dataAvailable = true;
        }
        
        // Get predicted label (same logic as calculateBurnout)
        if (!$predictedLabel) {
            $predictedLabel = ucfirst($assessment->Burnout_Category ?? $assessment->overall_risk ?? 'Unknown');
        }
        
        // Extract demographics (same as calculateBurnout output format)
        $name = $assessment->name ?? 'Unavailable';
        $age = $assessment->age ?? null;
        $gender = $assessment->gender ?? $assessment->sex ?? 'Unavailable';
        $program = $assessment->program ?? $assessment->college ?? 'Unavailable';
        $year_level = $assessment->year_level ?? $assessment->year ?? 'Unavailable';
        
        // Process result same way calculateBurnout does
        $resultData = ResultController::processResultForView($dataAvailable, $exhaustionCategory, $disengagementCategory, $barGraph, null);
        
        // Return same view with same variables (same as calculateBurnout)
        return view('assessment.result', compact(
            'assessment', 'responses', 'original_responses', 
            'name', 'age', 'gender', 'program', 'year_level',
            'exhaustionScore', 'disengagementScore', 'totalScore',
            'exhaustionAverage', 'disengagementAverage', 'exhaustionCategory', 'disengagementCategory',
            'interpretations', 'recommendations', 'predictedLabel', 'barGraph', 'dataAvailable'
        ))->with($resultData);
    }

    /**
     * Handle direct GET access to assessment result page
     * This route should only be accessed via POST after form submission
     */
    public function showResultError()
    {
        // Redirect to home page with error message
        return redirect()->route('assessment.index')->with('error', 'Please complete the assessment form first.');
    }
}