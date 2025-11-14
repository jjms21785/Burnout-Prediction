<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Mail\AssessmentEmail;
use App\Http\Controllers\ResultController;

/**
 * ViewController
 * Handles viewing assessment details and sending assessment emails
 */
class ViewController extends Controller
{
    /**
     * Get assessment details for view modal
     * Returns JSON data with assessment information including scores, levels, and recommendations
     */
    public function show($id)
    {
        try {
            $assessment = Assessment::findOrFail($id);
            
            // Process assessment data (extracts answers, calculates scores, gets interpretations/recommendations)
            $processedData = $this->processAssessmentData($assessment);
            
            // Get burnout category from stored ML prediction (no manual calculation)
            $category = $this->getBurnoutCategory($assessment);
            
            // Split name
            $name = $assessment->name ?? 'Unavailable';
            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0] ?? 'Unavailable';
            $lastName = $nameParts[1] ?? '';
            
            $data = [
                'id' => $assessment->id,
                'name' => $name,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $assessment->email ?? '',
                'age' => $assessment->age ?? 'Unavailable',
                'gender' => $assessment->sex ?? 'Unavailable',
                'program' => $assessment->college ?? 'Unavailable',
                'yearLevel' => $assessment->year ?? 'Unavailable',
                'category' => $category,
                'exhaustionLevel' => $processedData['exhaustionLevel'],
                'disengagementLevel' => $processedData['disengagementLevel'],
                'academicLevel' => $processedData['academicLevel'],
                'stressLevel' => $processedData['stressLevel'],
                'sleepLevel' => $processedData['sleepLevel'],
                'exhaustionScore' => $processedData['exhaustionScore'],
                'disengagementScore' => $processedData['disengagementScore'],
                'assessmentDate' => $assessment->created_at ? $assessment->created_at->format('M d, Y') : 'Unavailable',
                'interpretations' => $processedData['interpretations'],
                'recommendations' => $processedData['recommendations']
            ];
            
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Failed to fetch assessment view data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch assessment data'], 500);
        }
    }
    
    /**
     * Send assessment email to student
     * Sends follow-up email for counseling appointment with assessment category
     * Can send message only, schedule appointment only, or both
     */
    public function sendEmail(Request $request, $id)
    {
        // Check if at least one option is selected
        $sendMessage = $request->has('send_message') && $request->send_message == '1';
        $sendAppointment = $request->has('send_appointment') && $request->send_appointment == '1';
        
        if (!$sendMessage && !$sendAppointment) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one option: Send Message or Schedule Appointment'
            ], 422);
        }
        
        // Build validation rules
        $rules = [
            'email' => 'required|email'
        ];
        
        // Add conditional validation rules
        if ($sendMessage) {
            $rules['additional_message'] = 'required|string';
        }
        
        if ($sendAppointment) {
            $rules['appointment_datetime'] = 'required|date';
        }
        
        $request->validate($rules);
        
        try {
            $assessment = Assessment::findOrFail($id);
            
            // Update email if provided and different from existing
            if ($request->email && $assessment->email !== $request->email) {
                $assessment->email = $request->email;
                $assessment->save();
            }
            
            // Get burnout category from stored ML prediction (no manual calculation)
            $category = $this->getBurnoutCategory($assessment);
            
            // Prepare email data (only include selected options)
            $emailData = [
                'studentName' => $assessment->name ?? 'Student',
                'category' => $category,
                'sendMessage' => $sendMessage,
                'sendAppointment' => $sendAppointment
            ];
            
            // Only include message if option is selected
            if ($sendMessage) {
                $emailData['additionalMessage'] = $request->additional_message;
            }
            
            // Only include appointment if option is selected
            if ($sendAppointment) {
                $emailData['appointmentDatetime'] = $request->appointment_datetime;
            }
            
            // Send email
            Mail::to($request->email)->send(new AssessmentEmail($emailData));
            
            return response()->json([
                'success' => true,
                'message' => 'Assessment email sent successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send assessment email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send assessment email: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process assessment data: extract answers, calculate scores, and get interpretations/recommendations
     * This method is used by show() method to display detailed assessment information
     * 
     * @param Assessment $assessment The assessment model instance
     * @return array Contains exhaustionScore, disengagementScore, exhaustionLevel, disengagementLevel,
     *                academicLevel, stressLevel, sleepLevel, interpretations, and recommendations
     */
    private function processAssessmentData($assessment)
    {
        // Extract raw answers from database (works for both new submissions and imported data)
        $rawAnswers = $assessment->raw_answers ?? [];
        
        // Convert to simple array format [Q1, Q2, ..., Q30] for Python API
        $allAnswers = [];
        if (!empty($rawAnswers) && is_array($rawAnswers)) {
            // Check if it's in Q1-Q30 associative format
            if (isset($rawAnswers['Q1'])) {
                // Convert from {'Q1': 1, 'Q2': 2, ...} to [1, 2, ...]
                for ($i = 1; $i <= 30; $i++) {
                    $key = 'Q' . $i;
                    $allAnswers[] = isset($rawAnswers[$key]) ? (int) $rawAnswers[$key] : 0;
                }
            } elseif (isset($rawAnswers[0]) || isset($rawAnswers[1])) {
                // Already in simple array format [0, 1, 2, ...]
                $allAnswers = array_pad($rawAnswers, 30, 0);
                $allAnswers = array_slice($allAnswers, 0, 30);
                // Ensure all values are integers
                $allAnswers = array_map(function($val) {
                    return $val !== null && $val !== '' ? (int) $val : 0;
                }, $allAnswers);
            }
        }
        
        // Calculate exhaustion and disengagement scores from raw answers
        $exhaustionItems = ['Q16', 'Q17', 'Q20', 'Q21', 'Q23', 'Q25', 'Q28', 'Q29'];
        $disengagementItems = ['Q15', 'Q18', 'Q19', 'Q22', 'Q24', 'Q26', 'Q27', 'Q30'];
        
        // Use stored scores if available, otherwise calculate from answers
        $exhaustionScore = $assessment->Exhaustion;
        $disengagementScore = $assessment->Disengagement;
        
        // Recalculate scores from raw answers if scores are null/missing
        if (($exhaustionScore === null || $disengagementScore === null) && !empty($allAnswers)) {
            $responses = [];
            foreach ($allAnswers as $i => $answer) {
                $responses['Q' . ($i + 1)] = (int) $answer;
            }
            
            if (!empty($responses)) {
                if ($exhaustionScore === null) {
                    $exhaustionScore = array_sum(array_intersect_key($responses, array_flip($exhaustionItems)));
                }
                if ($disengagementScore === null) {
                    $disengagementScore = array_sum(array_intersect_key($responses, array_flip($disengagementItems)));
                }
            }
        }
        
        // Default to 0 if still null
        $exhaustionScore = $exhaustionScore ?? 0;
        $disengagementScore = $disengagementScore ?? 0;
        
        // Derive exhaustion and disengagement levels from ML prediction category (not from scores)
        // ML model: 0="Non-Burnout", 1="Exhausted", 2="Disengaged", 3="BURNOUT"
        $mlPredictionValue = $assessment->Burnout_Category;
        $exhaustionLevel = 'Low';
        $disengagementLevel = 'Low';
        
        if (is_numeric($mlPredictionValue)) {
            $categoryNum = (int)$mlPredictionValue;
            switch ($categoryNum) {
                case 0: // Non-Burnout = Low Exhaustion + Low Disengagement
                    $exhaustionLevel = 'Low';
                    $disengagementLevel = 'Low';
                    break;
                case 1: // Exhausted = High Exhaustion + Low Disengagement
                    $exhaustionLevel = 'High';
                    $disengagementLevel = 'Low';
                    break;
                case 2: // Disengaged = Low Exhaustion + High Disengagement
                    $exhaustionLevel = 'Low';
                    $disengagementLevel = 'High';
                    break;
                case 3: // BURNOUT = High Exhaustion + High Disengagement
                    $exhaustionLevel = 'High';
                    $disengagementLevel = 'High';
                    break;
            }
        }
        
        // Calculate averages for interpretations (if needed)
        $exhaustionAverage = count($exhaustionItems) > 0 ? $exhaustionScore / count($exhaustionItems) : 0;
        $disengagementAverage = count($disengagementItems) > 0 ? $disengagementScore / count($disengagementItems) : 0;
        
        // Process data on-demand: Always call Python API to get fresh calculations
        $pythonResponse = null;
        $processedData = null;
        $interpretations = null;
        $recommendations = null;
        $academicLevel = 'Unavailable';
        $stressLevel = 'Unavailable';
        $sleepLevel = 'Unavailable';
        
        // Only call API if we have valid answers
        if (!empty($allAnswers) && count($allAnswers) === 30) {
            try {
                $response = Http::timeout(10)->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])->post('http://127.0.0.1:5000/predict', [
                    'all_answers' => $allAnswers
                ]);
                
                if ($response->successful()) {
                    $pythonResponse = $response->json();
                    $processedData = ResultController::processPythonResponse($pythonResponse);
                    $interpretations = $processedData['interpretations'] ?? null;
                    $recommendations = $processedData['recommendations'] ?? null;
                    
                    // Extract codes for academic, stress, and sleep
                    $codes = $processedData['codes'] ?? [];
                    $academicCode = $codes['academic'] ?? null;
                    $stressCode = $codes['stress'] ?? null;
                    $sleepCode = $codes['sleep'] ?? null;
                    
                    // Map codes to levels
                    if ($academicCode === 'D1') {
                        $academicLevel = 'Good';
                    } elseif ($academicCode === 'D2') {
                        $academicLevel = 'Struggling';
                    }
                    
                    if ($stressCode === 'D3') {
                        $stressLevel = 'Low';
                    } elseif ($stressCode === 'D4') {
                        $stressLevel = 'Moderate';
                    } elseif ($stressCode === 'D5') {
                        $stressLevel = 'High';
                    }
                    
                    if ($sleepCode === 'D6') {
                        $sleepLevel = 'Good';
                    } elseif ($sleepCode === 'D7') {
                        $sleepLevel = 'Moderate';
                    } elseif ($sleepCode === 'D8') {
                        $sleepLevel = 'Poor';
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Python API call failed in ViewController::processAssessmentData(): ' . $e->getMessage());
                // Fall back to basic interpretations if API fails
            }
        }
        
        // Fallback: Generate basic interpretations if Python API failed or unavailable
        if (!$interpretations) {
            $interpretations = ResultController::generateBasicInterpretations($exhaustionLevel, $disengagementLevel, $exhaustionAverage, $disengagementAverage);
        }
        if (!$recommendations) {
            $recommendations = ResultController::generateBasicRecommendations($exhaustionLevel, $disengagementLevel);
        }
        
        return [
            'exhaustionScore' => $exhaustionScore,
            'disengagementScore' => $disengagementScore,
            'exhaustionLevel' => $exhaustionLevel,
            'disengagementLevel' => $disengagementLevel,
            'academicLevel' => $academicLevel,
            'stressLevel' => $stressLevel,
            'sleepLevel' => $sleepLevel,
            'interpretations' => $interpretations,
            'recommendations' => $recommendations
        ];
    }
    
    /**
     * Get burnout category from stored ML prediction
     */
    private function getBurnoutCategory($assessment)
    {
        return $assessment->getBurnoutCategoryLabel();
    }
}

