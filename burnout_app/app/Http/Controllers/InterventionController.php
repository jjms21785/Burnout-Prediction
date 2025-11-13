<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\InterventionMail;
use App\Http\Controllers\ResultController;

class InterventionController extends Controller
{
    /**
     * Get assessment details for intervention modal
     */
    public function show($id)
    {
        try {
            $assessment = Assessment::findOrFail($id);
            
            // Extract answers data
            $answersData = $assessment->answers;
            if (is_string($answersData)) {
                $answersData = json_decode($answersData, true) ?? [];
            }
            
            // Get python response if available
            $pythonResponse = $answersData['python_response'] ?? null;
            
            // Calculate exhaustion and disengagement levels
            $exhaustionAverage = 0;
            $disengagementAverage = 0;
            $exhaustionLevel = 'Low';
            $disengagementLevel = 'Low';
            
            $exhaustionItems = ['Q16', 'Q17', 'Q20', 'Q21', 'Q23', 'Q25', 'Q28', 'Q29'];
            $disengagementItems = ['Q15', 'Q18', 'Q19', 'Q22', 'Q24', 'Q26', 'Q27', 'Q30'];
            
            $exhaustionScore = $assessment->Exhaustion ?? 0;
            $disengagementScore = $assessment->Disengagement ?? 0;
            
            if (count($exhaustionItems) > 0) {
                $exhaustionAverage = $exhaustionScore / count($exhaustionItems);
            }
            if (count($disengagementItems) > 0) {
                $disengagementAverage = $disengagementScore / count($disengagementItems);
            }
            
            $exhaustionLevel = $exhaustionAverage >= 2.25 ? 'High' : 'Low';
            $disengagementLevel = $disengagementAverage >= 2.10 ? 'High' : 'Low';
            
            // Process interpretations and recommendations
            $interpretations = null;
            $recommendations = null;
            $academicLevel = 'Unavailable';
            $stressLevel = 'Unavailable';
            $sleepLevel = 'Unavailable';
            
            if ($pythonResponse) {
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
                
                // If codes not available, try to extract from interpretations breakdown
                if ($academicLevel === 'Unavailable' && $interpretations && isset($interpretations['breakdown']['academic'])) {
                    $academicTitle = $interpretations['breakdown']['academic']['title'] ?? '';
                    if (strpos($academicTitle, 'Good') !== false) {
                        $academicLevel = 'Good';
                    } elseif (strpos($academicTitle, 'Struggling') !== false) {
                        $academicLevel = 'Struggling';
                    }
                }
                
                if ($stressLevel === 'Unavailable' && $interpretations && isset($interpretations['breakdown']['stress'])) {
                    $stressTitle = $interpretations['breakdown']['stress']['title'] ?? '';
                    if (strpos($stressTitle, 'Low') !== false) {
                        $stressLevel = 'Low';
                    } elseif (strpos($stressTitle, 'Moderate') !== false) {
                        $stressLevel = 'Moderate';
                    } elseif (strpos($stressTitle, 'High') !== false) {
                        $stressLevel = 'High';
                    }
                }
                
                if ($sleepLevel === 'Unavailable' && $interpretations && isset($interpretations['breakdown']['sleep'])) {
                    $sleepTitle = $interpretations['breakdown']['sleep']['title'] ?? '';
                    if (strpos($sleepTitle, 'Good') !== false) {
                        $sleepLevel = 'Good';
                    } elseif (strpos($sleepTitle, 'Moderate') !== false) {
                        $sleepLevel = 'Moderate';
                    } elseif (strpos($sleepTitle, 'Poor') !== false) {
                        $sleepLevel = 'Poor';
                    }
                }
            } else {
                // Try to get stored interpretations and recommendations first
                $interpretations = $answersData['interpretations'] ?? $assessment->interpretations ?? null;
                $recommendations = $answersData['recommendations'] ?? $assessment->recommendations ?? null;
                
                // Try to extract levels from stored interpretations if available
                if ($interpretations && isset($interpretations['breakdown'])) {
                    if (isset($interpretations['breakdown']['academic'])) {
                        $academicTitle = $interpretations['breakdown']['academic']['title'] ?? '';
                        if (strpos($academicTitle, 'Good') !== false) {
                            $academicLevel = 'Good';
                        } elseif (strpos($academicTitle, 'Struggling') !== false) {
                            $academicLevel = 'Struggling';
                        }
                    }
                    
                    if (isset($interpretations['breakdown']['stress'])) {
                        $stressTitle = $interpretations['breakdown']['stress']['title'] ?? '';
                        if (strpos($stressTitle, 'Low') !== false) {
                            $stressLevel = 'Low';
                        } elseif (strpos($stressTitle, 'Moderate') !== false) {
                            $stressLevel = 'Moderate';
                        } elseif (strpos($stressTitle, 'High') !== false) {
                            $stressLevel = 'High';
                        }
                    }
                    
                    if (isset($interpretations['breakdown']['sleep'])) {
                        $sleepTitle = $interpretations['breakdown']['sleep']['title'] ?? '';
                        if (strpos($sleepTitle, 'Good') !== false) {
                            $sleepLevel = 'Good';
                        } elseif (strpos($sleepTitle, 'Moderate') !== false) {
                            $sleepLevel = 'Moderate';
                        } elseif (strpos($sleepTitle, 'Poor') !== false) {
                            $sleepLevel = 'Poor';
                        }
                    }
                }
                
                // Generate basic interpretations and recommendations if not available
                if (!$interpretations) {
                    $interpretations = ResultController::generateBasicInterpretations($exhaustionLevel, $disengagementLevel, $exhaustionAverage, $disengagementAverage);
                }
                if (!$recommendations) {
                    $recommendations = ResultController::generateBasicRecommendations($exhaustionLevel, $disengagementLevel);
                }
            }
            
            // Final fallback: extract levels from interpretations if still unavailable
            if (($academicLevel === 'Unavailable' || $stressLevel === 'Unavailable' || $sleepLevel === 'Unavailable') && $interpretations && isset($interpretations['breakdown'])) {
                if ($academicLevel === 'Unavailable' && isset($interpretations['breakdown']['academic'])) {
                    $academicTitle = $interpretations['breakdown']['academic']['title'] ?? '';
                    if (strpos($academicTitle, 'Good') !== false) {
                        $academicLevel = 'Good';
                    } elseif (strpos($academicTitle, 'Struggling') !== false) {
                        $academicLevel = 'Struggling';
                    }
                }
                
                if ($stressLevel === 'Unavailable' && isset($interpretations['breakdown']['stress'])) {
                    $stressTitle = $interpretations['breakdown']['stress']['title'] ?? '';
                    if (strpos($stressTitle, 'Low') !== false) {
                        $stressLevel = 'Low';
                    } elseif (strpos($stressTitle, 'Moderate') !== false) {
                        $stressLevel = 'Moderate';
                    } elseif (strpos($stressTitle, 'High') !== false) {
                        $stressLevel = 'High';
                    }
                }
                
                if ($sleepLevel === 'Unavailable' && isset($interpretations['breakdown']['sleep'])) {
                    $sleepTitle = $interpretations['breakdown']['sleep']['title'] ?? '';
                    if (strpos($sleepTitle, 'Good') !== false) {
                        $sleepLevel = 'Good';
                    } elseif (strpos($sleepTitle, 'Moderate') !== false) {
                        $sleepLevel = 'Moderate';
                    } elseif (strpos($sleepTitle, 'Poor') !== false) {
                        $sleepLevel = 'Poor';
                    }
                }
            }
            
            // Calculate burnout category from scores
            $category = $this->calculateBurnoutCategory($assessment);
            
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
                'exhaustionLevel' => $exhaustionLevel,
                'disengagementLevel' => $disengagementLevel,
                'academicLevel' => $academicLevel,
                'stressLevel' => $stressLevel,
                'sleepLevel' => $sleepLevel,
                'exhaustionScore' => $assessment->Exhaustion ?? 0,
                'disengagementScore' => $assessment->Disengagement ?? 0,
                'assessmentDate' => $assessment->created_at ? $assessment->created_at->format('M d, Y') : 'Unavailable',
                'interpretations' => $interpretations,
                'recommendations' => $recommendations
            ];
            
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Failed to fetch intervention data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch assessment data'], 500);
        }
    }
    
    /**
     * Send intervention email
     */
    public function sendIntervention(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'appointment_datetime' => 'nullable|date',
            'additional_message' => 'nullable|string'
        ]);
        
        try {
            $assessment = Assessment::findOrFail($id);
            
            // Update email if provided and different from existing
            if ($request->email && $assessment->email !== $request->email) {
                $assessment->email = $request->email;
                $assessment->save();
            }
            
            // Extract recommendations
            $answersData = $assessment->answers;
            if (is_string($answersData)) {
                $answersData = json_decode($answersData, true) ?? [];
            }
            $recommendations = $answersData['recommendations'] ?? $assessment->recommendations ?? [];
            
            // Calculate burnout category
            $category = $this->calculateBurnoutCategory($assessment);
            
            // Prepare email data
            $emailData = [
                'studentName' => $assessment->name ?? 'Student',
                'category' => $category,
                'exhaustionScore' => $assessment->Exhaustion ?? 0,
                'disengagementScore' => $assessment->Disengagement ?? 0,
                'recommendations' => $recommendations,
                'appointmentDatetime' => $request->appointment_datetime,
                'additionalMessage' => $request->additional_message
            ];
            
            // Send email
            Mail::to($request->email)->send(new InterventionMail($emailData));
            
            return response()->json([
                'success' => true,
                'message' => 'Intervention email sent successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send intervention email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send intervention email: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Calculate burnout category from assessment
     */
    private function calculateBurnoutCategory($assessment)
    {
        $exhaustion = $assessment->Exhaustion ?? 0;
        $disengagement = $assessment->Disengagement ?? 0;
        
        $highExhaustion = $exhaustion >= 18;
        $highDisengagement = $disengagement >= 17;
        
        if (!$highExhaustion && !$highDisengagement) {
            return 'Low Burnout';
        } elseif (!$highExhaustion && $highDisengagement) {
            return 'Disengaged';
        } elseif ($highExhaustion && !$highDisengagement) {
            return 'Exhausted';
        } else {
            return 'High Burnout';
        }
    }
}

