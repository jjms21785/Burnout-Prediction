<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    public function index()
    {
        $totalRecords = Assessment::count();
        
        // Get files from imports directory
        $files = [];
        $importPath = storage_path('app/imports');
        
        if (is_dir($importPath)) {
            $allFiles = scandir($importPath);
            foreach ($allFiles as $file) {
                if ($file !== '.' && $file !== '..' && is_file($importPath . '/' . $file)) {
                    $files[] = [
                        'name' => $file,
                        'path' => $importPath . '/' . $file,
                        'size' => filesize($importPath . '/' . $file),
                        'date' => filemtime($importPath . '/' . $file),
                        'extension' => pathinfo($file, PATHINFO_EXTENSION)
                    ];
                }
            }
            // Sort by date, newest first
            usort($files, function($a, $b) {
                return $b['date'] - $a['date'];
            });
        }
        
        return view('admin.files', compact('totalRecords', 'files'));
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $imported = 0;
        $errors = [];

        try {
            // Save the file to storage
            $importPath = storage_path('app/imports');
            if (!is_dir($importPath)) {
                mkdir($importPath, 0755, true);
            }
            
            $originalName = $file->getClientOriginalName();
            $fileName = $originalName;
            
            // If file with same name exists, add a counter to avoid overwriting
            $counter = 1;
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
            while (file_exists($importPath . '/' . $fileName)) {
                $fileName = $baseName . '_' . $counter . '.' . $fileExtension;
                $counter++;
            }
            
            $file->move($importPath, $fileName);

            // Parse the file
            $extension = strtolower($file->getClientOriginalExtension());
            
            if ($extension === 'csv') {
                $handle = fopen($importPath . '/' . $fileName, 'r');
                if ($handle === false) {
                    throw new \Exception('Could not open CSV file');
                }
                
                // Read header - handle BOM if present
                $header = fgetcsv($handle);
                if ($header && isset($header[0]) && substr($header[0], 0, 3) === "\xEF\xBB\xBF") {
                    $header[0] = substr($header[0], 3);
                }
                
                // Normalize header keys
                $header = array_map(function($h) {
                    return strtolower(trim($h));
                }, $header);
                
                $rowNum = 1;
                while (($row = fgetcsv($handle)) !== false) {
                    $rowNum++;
                    try {
                        if (count($row) !== count($header)) {
                            // Pad or trim row to match header
                            $row = array_pad($row, count($header), '');
                            $row = array_slice($row, 0, count($header));
                        }
                        
                        $data = array_combine($header, $row);
                        if ($data === false) {
                            $errors[] = "Row $rowNum: Column count mismatch";
                            continue;
                        }
                        
                        $this->importAssessmentRow($data);
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Row $rowNum: " . $e->getMessage();
                    }
                }
                fclose($handle);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                // For Excel files, try to use PhpSpreadsheet if available, otherwise use simple CSV reading
                // Check if PhpSpreadsheet is available
                if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($importPath . '/' . $fileName);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();
                    
                    if (empty($rows)) {
                        throw new \Exception('Excel file is empty');
                    }
                    
                    // Get header (first row)
                    $header = array_map(function($h) {
                        return strtolower(trim($h));
                    }, array_shift($rows));
                    
                    $rowNum = 1;
                    foreach ($rows as $row) {
                        $rowNum++;
                        try {
                            // Pad row to match header length
                            $row = array_pad($row, count($header), '');
                            $row = array_slice($row, 0, count($header));
                            
                        $data = array_combine($header, $row);
                            if ($data === false) {
                                $errors[] = "Row $rowNum: Column count mismatch";
                                continue;
                            }
                            
                        $this->importAssessmentRow($data);
                        $imported++;
                    } catch (\Exception $e) {
                            $errors[] = "Row $rowNum: " . $e->getMessage();
                    }
                    }
                } else {
                    // Fallback: Try reading as CSV (won't work well for real Excel, but handles basic cases)
                    $errors[] = "Excel parsing requires PhpSpreadsheet. Please install: composer require phpoffice/phpspreadsheet";
                }
            }

            // Only return error message if there are errors
            if (count($errors) > 0) {
                $errorMessage = count($errors) . " error(s) occurred during import: " . implode("; ", array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $errorMessage .= " (and " . (count($errors) - 10) . " more)";
                }
                return redirect()->route('admin.files')->with('error', $errorMessage);
            }
            
            // No errors - just refresh the page silently
            return redirect()->route('admin.files');
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            return redirect()->route('admin.files')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function exportData(Request $request)
    {
        $format = $request->input('format', 'csv');
        $assessments = Assessment::all();

        // Helper function to split name into first_name and last_name
        $splitName = function($name) {
            if (empty($name) || $name === 'unavailable') {
                return ['unavailable', 'unavailable'];
            }
            // Check if it's an Anonymous name
            if (preg_match('/^Anonymous\d+$/', $name)) {
                return ['unavailable', 'unavailable'];
            }
            $parts = explode(' ', trim($name), 2);
            return [
                $parts[0] ?? 'unavailable',
                $parts[1] ?? 'unavailable'
            ];
        };

        // Helper function to get value or "unavailable"
        $getValue = function($value) {
            if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
                return 'unavailable';
            }
            return $value;
        };

        // Helper function to map overall_risk and scores to Category
        // Category 0 (Non-Burnout): low exhaustion AND low disengagement
        // Category 1 (Disengaged): low exhaustion AND HIGH disengagement
        // Category 2 (Exhausted): HIGH exhaustion AND low disengagement
        // Category 3 (BURNOUT): HIGH exhaustion AND HIGH disengagement
        $getBurnoutCategory = function($assessment) {
            // If we have scores, calculate Category directly
            // Use new column names with fallback to old names
            $exhaustionScore = $assessment->Exhaustion ?? $assessment->exhaustion_score ?? null;
            $disengagementScore = $assessment->Disengagement ?? $assessment->disengagement_score ?? null;
            
            if ($exhaustionScore !== null && $disengagementScore !== null) {
                $highExhaustionThreshold = 18; // 2.25 average * 8
                $highDisengagementThreshold = 17; // 2.1 average * 8
                
                $highExhaustion = $exhaustionScore >= $highExhaustionThreshold;
                $highDisengagement = $disengagementScore >= $highDisengagementThreshold;
                
                if (!$highExhaustion && !$highDisengagement) {
                    return 0; // Non-Burnout
                } elseif (!$highExhaustion && $highDisengagement) {
                    return 1; // Disengaged
                } elseif ($highExhaustion && !$highDisengagement) {
                    return 2; // Exhausted
                } else {
                    return 3; // BURNOUT
                }
            }
            
            // Fallback: map from overall_risk (less accurate)
            $overallRisk = $assessment->Burnout_Category ?? $assessment->overall_risk ?? null;
            if (empty($overallRisk) || $overallRisk === 'unavailable') {
                return 'unavailable';
            }
            $risk = strtolower($overallRisk);
            if ($risk === 'low') return 0;
            if ($risk === 'moderate') return 1; // Can't distinguish 1 vs 2, default to 1
            if ($risk === 'high') return 3; // BURNOUT
            return 'unavailable';
        };

        if ($format === 'csv' || $format === 'xlsx') {
            $extension = $format === 'csv' ? 'csv' : 'xlsx';
            $filename = 'burnalytics_data_' . date('Y-m-d') . '.' . $extension;
            $contentType = $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            
            $headers = [
                'Content-Type' => $contentType,
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() use ($assessments, $splitName, $getValue, $getBurnoutCategory) {
                $file = fopen('php://output', 'w');
                
                // CSV Header in the exact format requested
                $header = ['first_name', 'last_name', 'sex', 'age', 'year', 'college'];
                for ($i = 1; $i <= 30; $i++) {
                    $header[] = 'Q' . $i;
                }
                $header[] = 'Exhaustion';
                $header[] = 'Disengagement';
                $header[] = 'Category';
                
                fputcsv($file, $header);

                // Data rows
                foreach ($assessments as $assessment) {
                    list($firstName, $lastName) = $splitName($assessment->name);
                    
                    // Use new column names with fallback to old names
                    $gender = $assessment->sex ?? $assessment->gender ?? null;
                    $year = $assessment->year ?? $assessment->year_level ?? null;
                    $program = $assessment->college ?? $assessment->program ?? null;
                    $exhaustionScore = $assessment->Exhaustion ?? $assessment->exhaustion_score ?? null;
                    $disengagementScore = $assessment->Disengagement ?? $assessment->disengagement_score ?? null;
                    
                    $row = [
                        $getValue($firstName),
                        $getValue($lastName),
                        $getValue($gender),
                        $getValue($assessment->age),
                        $getValue($year),
                        $getValue($program),
                    ];
                    
                    // Add Q1-Q30 from answers array
                    // Use raw_answers accessor for backward compatibility (handles both old and new formats)
                    $answers = $assessment->raw_answers ?? [];
                    for ($i = 1; $i <= 30; $i++) {
                        $qIndex = $i - 1; // Array is 0-indexed
                        $row[] = $getValue($answers[$qIndex] ?? null);
                    }
                    
                    // Add Exhaustion and Disengagement (as averages, dividing by 8 if score exists)
                    if ($exhaustionScore !== null) {
                        $exhaustion = number_format($exhaustionScore / 8.0, 3, '.', '');
                        $row[] = (float)$exhaustion;
                    } else {
                        $row[] = 'unavailable';
                    }
                    
                    if ($disengagementScore !== null) {
                        $disengagement = number_format($disengagementScore / 8.0, 3, '.', '');
                        $row[] = (float)$disengagement;
                    } else {
                        $row[] = 'unavailable';
                    }
                    
                    // Add Category (calculated from exhaustion/disengagement scores or mapped from overall_risk)
                    // Category 0 = Non-Burnout, 1 = Disengaged, 2 = Exhausted, 3 = BURNOUT
                    $row[] = $getBurnoutCategory($assessment);
                    
                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }
        
        return back()->with('error', 'Invalid export format');
    }

    public function downloadFile($filename)
    {
        $filePath = storage_path('app/imports/' . $filename);
        
        if (!file_exists($filePath)) {
            return redirect()->route('admin.files')->with('error', 'File not found.');
        }

        return response()->download($filePath);
    }

    public function deleteFile($filename)
    {
        $filePath = storage_path('app/imports/' . $filename);
        
        if (!file_exists($filePath)) {
            return redirect()->route('admin.files')->with('error', 'File not found.');
        }

        try {
            unlink($filePath);
            return redirect()->route('admin.files');
        } catch (\Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());
            return redirect()->route('admin.files')->with('error', 'Failed to delete file: ' . $e->getMessage());
        }
    }

    private function importAssessmentRow($data)
    {
        // Normalize data array keys (handle spaces, case variations)
        $normalized = [];
        foreach ($data as $key => $value) {
            $normalized[strtolower(trim($key))] = $value;
        }
        $data = $normalized;

        // Helper function to get value or null if "unavailable"
        $getValue = function($value) {
            if ($value === null || $value === '' || (is_string($value) && strtolower(trim($value)) === 'unavailable')) {
                return null;
            }
            return trim($value);
        };

        // Handle first_name and last_name - combine them into name
        $firstName = $getValue($data['first_name'] ?? null);
        $lastName = $getValue($data['last_name'] ?? null);
        
        // Also support legacy formats (name, full_name)
        $name = $getValue($data['name'] ?? $data['full_name'] ?? null);
        
        if (empty($name)) {
            if (!empty($firstName) || !empty($lastName)) {
                $name = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));
            }
        }
        
        if (empty($name)) {
            // Generate anonymous name if missing
            $lastAnon = Assessment::where('name', 'like', 'Anonymous%')->orderByDesc('id')->first();
            $anonNum = 1;
            if ($lastAnon && preg_match('/Anonymous(\d+)/', $lastAnon->name, $m)) {
                $anonNum = intval($m[1]) + 1;
            }
            $name = 'Anonymous' . $anonNum;
        }

        // Map age - handle "unavailable"
        $ageValue = $getValue($data['age'] ?? null);
        $age = !empty($ageValue) && is_numeric($ageValue) ? (int)$ageValue : null;
        
        // Map gender/sex - support 'sex' column (preferred format)
        $gender = $getValue($data['sex'] ?? $data['gender'] ?? null);
        if (empty($gender)) {
            $gender = 'unavailable';
        }
        
        // Map program/college - support 'college' column (preferred format)
        $program = $getValue($data['college'] ?? $data['program'] ?? $data['department'] ?? null);
        if (empty($program)) {
            $program = 'unavailable';
        }
        
        // Map year_level/year - support 'year' column (preferred format)
        $yearLevel = $getValue($data['year'] ?? $data['year_level'] ?? $data['yearlevel'] ?? $data['grade'] ?? null);
        if (empty($yearLevel)) {
            $yearLevel = 'unavailable';
        }
        
        // Map Category to overall_risk
        // Category mapping based on exhaustion and disengagement levels:
        // Category 0 (Non-Burnout): low exhaustion AND low disengagement -> overall_risk = 'low'
        // Category 1 (Disengaged): low exhaustion AND HIGH disengagement -> overall_risk = 'moderate'
        // Category 2 (Exhausted): HIGH exhaustion AND low disengagement -> overall_risk = 'moderate'
        // Category 3 (BURNOUT): HIGH exhaustion AND HIGH disengagement -> overall_risk = 'high'
        // Also supports Category column (preferred) and burnout_category (backward compatibility)
        $overallRisk = null;
        if (isset($data['category'])) {
            $burnoutCat = $data['category'];
            if ($burnoutCat === 'unavailable' || $burnoutCat === '' || $burnoutCat === null) {
                $overallRisk = null;
            } elseif (is_numeric($burnoutCat)) {
                $burnoutCatNum = (int)$burnoutCat;
                if ($burnoutCatNum === 0) {
                    $overallRisk = 'low'; // Non-Burnout: low exhaustion and low disengagement
                } elseif ($burnoutCatNum === 1) {
                    $overallRisk = 'moderate'; // Disengaged: low exhaustion and HIGH disengagement
                } elseif ($burnoutCatNum === 2) {
                    $overallRisk = 'moderate'; // Exhausted: HIGH exhaustion and low disengagement
                } elseif ($burnoutCatNum === 3) {
                    $overallRisk = 'high'; // BURNOUT: HIGH exhaustion and HIGH disengagement
                }
            } else {
                // Try string format
                $riskValue = strtolower(trim($burnoutCat));
                if (in_array($riskValue, ['low', 'moderate', 'high'])) {
                    $overallRisk = $riskValue;
                }
            }
        } elseif (isset($data['burnout_category'])) {
            // Backward compatibility: also check burnout_category
            $burnoutCat = $data['burnout_category'];
            if ($burnoutCat === 'unavailable' || $burnoutCat === '' || $burnoutCat === null) {
                $overallRisk = null;
            } elseif (is_numeric($burnoutCat)) {
                $burnoutCatNum = (int)$burnoutCat;
                if ($burnoutCatNum === 0) {
                    $overallRisk = 'low';
                } elseif ($burnoutCatNum === 1) {
                    $overallRisk = 'moderate';
                } elseif ($burnoutCatNum === 2) {
                    $overallRisk = 'moderate';
                } elseif ($burnoutCatNum === 3) {
                    $overallRisk = 'high';
                }
            } else {
                // Try string format
                $riskValue = strtolower(trim($burnoutCat));
                if (in_array($riskValue, ['low', 'moderate', 'high'])) {
                    $overallRisk = $riskValue;
                }
            }
        } elseif (isset($data['overall_risk'])) {
            $riskValue = strtolower(trim($data['overall_risk']));
            if (in_array($riskValue, ['low', 'moderate', 'high']) && $riskValue !== 'unavailable') {
                $overallRisk = $riskValue;
            }
        } elseif (isset($data['risk'])) {
            $riskValue = strtolower(trim($data['risk']));
            if (in_array($riskValue, ['low', 'moderate', 'high']) && $riskValue !== 'unavailable') {
                $overallRisk = $riskValue;
            }
        }

        // Extract question answers (Q1-Q30)
        $answers = [];
        for ($i = 1; $i <= 30; $i++) {
            $qKey = 'q' . $i;
            if (isset($data[$qKey])) {
                $answerValue = $data[$qKey];
                // Handle "unavailable" or empty values
                if ($answerValue === 'unavailable' || $answerValue === '' || $answerValue === null) {
                    $answers[] = null;
                } elseif (is_numeric($answerValue)) {
                    $answers[] = (int)$answerValue;
                } else {
                    $answers[] = null;
                }
            } else {
                $answers[] = null;
            }
        }
        
        // If no Q1-Q30 found, check for other answer formats (legacy support)
        $hasAnswers = false;
        foreach ($answers as $a) {
            if ($a !== null) {
                $hasAnswers = true;
                break;
            }
        }
        
        if (!$hasAnswers && isset($data['answers'])) {
            // If answers is a JSON string, decode it
            if (is_string($data['answers'])) {
                $decoded = json_decode($data['answers'], true);
                if (is_array($decoded)) {
                    $answers = array_pad($decoded, 30, null);
                    $answers = array_slice($answers, 0, 30);
                }
            } elseif (is_array($data['answers'])) {
                $answers = array_pad($data['answers'], 30, null);
                $answers = array_slice($answers, 0, 30);
            }
        }

        // Handle Exhaustion and Disengagement scores
        // They come as floats (averages), so multiply by 8 to get sum score
        $exhaustionScore = null;
        if (isset($data['exhaustion']) && $data['exhaustion'] !== '' && $data['exhaustion'] !== 'unavailable') {
            $exhaustionValue = (float)$data['exhaustion'];
            // Convert float average to integer sum (multiply by 8 for OLBI scale)
            $exhaustionScore = (int)round($exhaustionValue * 8);
        } elseif (isset($data['exhaustion_score']) && $data['exhaustion_score'] !== '' && $data['exhaustion_score'] !== 'unavailable') {
            $exhaustionScore = (int)$data['exhaustion_score'];
        }
        
        $disengagementScore = null;
        if (isset($data['disengagement']) && $data['disengagement'] !== '' && $data['disengagement'] !== 'unavailable') {
            $disengagementValue = (float)$data['disengagement'];
            // Convert float average to integer sum (multiply by 8 for OLBI scale)
            $disengagementScore = (int)round($disengagementValue * 8);
        } elseif (isset($data['disengagement_score']) && $data['disengagement_score'] !== '' && $data['disengagement_score'] !== 'unavailable') {
            $disengagementScore = (int)$data['disengagement_score'];
        }
        
        // If olbi_score exists but exhaustion/disengagement don't, split it (approximation)
        if ($exhaustionScore === null && $disengagementScore === null && isset($data['olbi_score']) && $data['olbi_score'] !== 'unavailable') {
            $olbiScore = (int)$data['olbi_score'];
            $exhaustionScore = round($olbiScore / 2);
            $disengagementScore = $olbiScore - $exhaustionScore;
        }

        $confidence = isset($data['confidence']) && $data['confidence'] !== 'unavailable' ? (float)$data['confidence'] : null;

        // Create assessment - allow "unavailable" values but convert null required fields
        if (empty($age)) {
            throw new \Exception('Missing required field: age');
        }
        if (empty($gender) || $gender === 'unavailable') {
            throw new \Exception('Missing required field: gender/sex');
        }
        if (empty($program) || $program === 'unavailable') {
            throw new \Exception('Missing required field: program/college');
        }
        if (empty($yearLevel) || $yearLevel === 'unavailable') {
            throw new \Exception('Missing required field: year_level/year');
        }

        Assessment::create([
            'name' => $name,
            'age' => $age,
            'sex' => $gender, // Map gender -> sex
            'college' => $program, // Map program -> college
            'year' => $yearLevel, // Map year_level -> year
            'Burnout_Category' => $overallRisk, // Map overall_risk -> Burnout_Category
            'Exhaustion' => $exhaustionScore, // Map exhaustion_score -> Exhaustion
            'Disengagement' => $disengagementScore, // Map disengagement_score -> Disengagement
            'confidence' => $confidence,
            'answers' => $answers,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

