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
        $files = [];
        $archivedFiles = [];
        $importPath = storage_path('app/imports');
        $archivePath = storage_path('app/imports/archives');
        
        // Ensure archive directory exists
        if (!is_dir($archivePath)) {
            mkdir($archivePath, 0755, true);
        }
        
        if (is_dir($importPath)) {
            $allFiles = scandir($importPath);
            foreach ($allFiles as $file) {
                if ($file !== '.' && $file !== '..' && $file !== 'archives' && is_file($importPath . '/' . $file)) {
                    $files[] = [
                        'name' => $file,
                        'path' => $importPath . '/' . $file,
                        'size' => filesize($importPath . '/' . $file),
                        'date' => filemtime($importPath . '/' . $file),
                        'extension' => pathinfo($file, PATHINFO_EXTENSION)
                    ];
                }
            }
            usort($files, fn($a, $b) => $b['date'] - $a['date']);
        }
        
        // Get archived files
        if (is_dir($archivePath)) {
            $allArchivedFiles = scandir($archivePath);
            foreach ($allArchivedFiles as $file) {
                if ($file !== '.' && $file !== '..' && is_file($archivePath . '/' . $file)) {
                    $archivedFiles[] = [
                        'name' => $file,
                        'path' => $archivePath . '/' . $file,
                        'size' => filesize($archivePath . '/' . $file),
                        'date' => filemtime($archivePath . '/' . $file),
                        'extension' => pathinfo($file, PATHINFO_EXTENSION)
                    ];
                }
            }
            usort($archivedFiles, fn($a, $b) => $b['date'] - $a['date']);
        }
        
        return view('admin.files', compact('totalRecords', 'files', 'archivedFiles'));
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        try {
            $filePath = $this->saveFile($request->file('file'));
            $result = $this->processFile($filePath);
            
            if (count($result['errors']) > 0) {
                return $this->handleErrorResponse($request, $result['errors'], $result['imported']);
            }
            
            return $this->handleSuccessResponse($request);
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            return $this->handleErrorResponse($request, [$e->getMessage()], 0);
        }
    }

    private function saveFile($file)
    {
            $importPath = storage_path('app/imports');
            if (!is_dir($importPath)) {
                mkdir($importPath, 0755, true);
            }
            
            $originalName = $file->getClientOriginalName();
            $fileName = $originalName;
            $counter = 1;
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
        
            while (file_exists($importPath . '/' . $fileName)) {
                $fileName = $baseName . '_' . $counter . '.' . $fileExtension;
                $counter++;
            }
            
            $file->move($importPath, $fileName);
        return $importPath . '/' . $fileName;
    }

    private function processFile($filePath)
    {
        $imported = 0;
        $errors = [];
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            if ($extension === 'csv') {
            return $this->processCsv($filePath, $imported, $errors);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            return $this->processExcel($filePath, $imported, $errors);
        }
        
        throw new \Exception('Unsupported file format');
    }

    private function processCsv($filePath, $imported, $errors)
    {
        $handle = fopen($filePath, 'r');
                if ($handle === false) {
                    throw new \Exception('Could not open CSV file');
                }
                
        $header = $this->normalizeHeader(fgetcsv($handle));
        $rowNum = 1;
        
                while (($row = fgetcsv($handle)) !== false) {
                    $rowNum++;
            $result = $this->processRow($header, $row, $rowNum);
            if ($result['success']) {
                $imported++;
            } else {
                $errors[] = $result['error'];
            }
        }
        
        fclose($handle);
        return compact('imported', 'errors');
    }

    private function processExcel($filePath, $imported, $errors)
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            throw new \Exception('Excel parsing requires PhpSpreadsheet. Please install: composer require phpoffice/phpspreadsheet');
        }
        
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray();
                    
                    if (empty($rows)) {
                        throw new \Exception('Excel file is empty');
                    }
                    
        $header = $this->normalizeHeader(array_shift($rows));
        $rowNum = 1;
        
                    foreach ($rows as $row) {
                        $rowNum++;
            $result = $this->processRow($header, $row, $rowNum);
            if ($result['success']) {
                $imported++;
            } else {
                $errors[] = $result['error'];
            }
        }
        
        return compact('imported', 'errors');
    }

    private function normalizeHeader($header)
    {
        if ($header && isset($header[0]) && substr($header[0], 0, 3) === "\xEF\xBB\xBF") {
            $header[0] = substr($header[0], 3);
        }
        return array_map(fn($h) => strtolower(trim($h)), $header);
    }

    private function processRow($header, $row, $rowNum)
    {
        try {
            if (count($row) !== count($header)) {
                            $row = array_pad($row, count($header), '');
                            $row = array_slice($row, 0, count($header));
            }
                            
                        $data = array_combine($header, $row);
                            if ($data === false) {
                return ['success' => false, 'error' => "Row $rowNum: Column count mismatch"];
                            }
                            
                        $this->importAssessmentRow($data);
            return ['success' => true];
                    } catch (\Exception $e) {
            return ['success' => false, 'error' => "Row $rowNum: " . $e->getMessage()];
        }
    }

    private function handleSuccessResponse($request)
    {
            if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('admin.files');
    }

    private function handleErrorResponse($request, $errors, $imported = 0)
    {
        $errorMessage = $this->formatErrorMessage($errors);
        
        if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => $errors
                    ]);
                }
                
                return redirect()->route('admin.files')->with('error', $errorMessage);
            }
            
    private function formatErrorMessage($errors)
    {
        if (count($errors) === 1) {
            return $errors[0];
        }
        
        $message = count($errors) . " error(s) occurred during import: " . implode("; ", array_slice($errors, 0, 10));
        if (count($errors) > 10) {
            $message .= " (and " . (count($errors) - 10) . " more)";
        }
        
        return $message;
    }

    public function exportData(Request $request)
    {
        $format = $request->input('format', 'csv');
        $assessments = Assessment::all();

        if ($format === 'csv') {
            return $this->exportCsv($assessments);
        } elseif ($format === 'json') {
            return response()->json($assessments->map(function($assessment) {
                return [
                    'id' => $assessment->id,
                    'name' => $assessment->name,
                    'sex' => $assessment->sex,
                    'age' => $assessment->age,
                    'year' => $assessment->year,
                    'college' => $assessment->college,
                    'answers' => $assessment->answers,
                    'Exhaustion' => $assessment->Exhaustion,
                    'Disengagement' => $assessment->Disengagement,
                    'Burnout_Category' => $assessment->Burnout_Category
                ];
            })->toArray());
        }
        
        return back()->with('error', 'Invalid export format');
    }

    private function exportCsv($assessments)
    {
            $filename = 'burnalytics_data_' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

        $callback = function() use ($assessments) {
                $file = fopen('php://output', 'w');
            $this->writeCsvHeader($file);
            
            foreach ($assessments as $assessment) {
                $this->writeCsvRow($file, $assessment);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function writeCsvHeader($file)
    {
                $header = ['first_name', 'last_name', 'sex', 'age', 'year', 'college'];
                for ($i = 1; $i <= 30; $i++) {
                    $header[] = 'Q' . $i;
                }
                $header[] = 'Exhaustion';
                $header[] = 'Disengagement';
                $header[] = 'Category';
        fputcsv($file, $header);
    }

    private function writeCsvRow($file, $assessment)
    {
        $splitName = fn($name) => $this->splitName($name);
        $getValue = fn($value) => $this->getExportValue($value);
        $getBurnoutCategory = fn($assessment) => $this->getBurnoutCategory($assessment);
        
                    list($firstName, $lastName) = $splitName($assessment->name);
                    
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
                    
                    $answers = $assessment->raw_answers ?? [];
                    for ($i = 1; $i <= 30; $i++) {
            $row[] = $getValue($answers[$i - 1] ?? null);
        }
        
        $row[] = $exhaustionScore !== null ? (float)number_format($exhaustionScore / 8.0, 3, '.', '') : 'unavailable';
        $row[] = $disengagementScore !== null ? (float)number_format($disengagementScore / 8.0, 3, '.', '') : 'unavailable';
                    $row[] = $getBurnoutCategory($assessment);
                    
                    fputcsv($file, $row);
                }

    private function splitName($name)
    {
        if (empty($name) || $name === 'unavailable' || preg_match('/^Anonymous\d+$/', $name)) {
            return ['unavailable', 'unavailable'];
        }
        $parts = explode(' ', trim($name), 2);
        return [$parts[0] ?? 'unavailable', $parts[1] ?? 'unavailable'];
    }

    private function getExportValue($value)
    {
        return ($value === null || $value === '' || (is_string($value) && trim($value) === '')) ? 'unavailable' : $value;
    }

    /**
     * Get burnout category from stored ML prediction
     * Returns numeric value (0,1,2,3) for export purposes
     * NO manual calculation - only reads from database
     */
    private function getBurnoutCategory($assessment)
    {
        // Read directly from stored ML prediction (no manual calculation)
        $category = $assessment->Burnout_Category;
        
        if ($category === null || $category === '' || $category === 'unavailable') {
            return 'unavailable';
        }
        
        // If it's already numeric (0, 1, 2, 3), return as is
        if (is_numeric($category)) {
            $categoryNum = (int)$category;
            if ($categoryNum >= 0 && $categoryNum <= 3) {
                return $categoryNum;
            }
            return 'unavailable';
        }
        
        // If it's a label string, convert to numeric for backward compatibility
        $categoryLower = strtolower(trim($category));
        $categoryMap = [
            'low' => 0,
            'non-burnout' => 0,
            'low burnout' => 0,
            'disengaged' => 1,
            'exhausted' => 2,
            'high' => 3,
            'burnout' => 3,
            'high burnout' => 3
        ];
        
        return $categoryMap[$categoryLower] ?? 'unavailable';
    }

    public function downloadFile($filename)
    {
        // Check if it's an archived file (contains 'archives/' in the path)
        if (strpos($filename, 'archives/') === 0) {
            $filePath = storage_path('app/imports/' . $filename);
        } else {
            $filePath = storage_path('app/imports/' . $filename);
        }
        
        if (!file_exists($filePath)) {
            return redirect()->route('admin.files')->with('error', 'File not found.');
        }

        return response()->download($filePath);
    }

    public function deleteFile($filename)
    {
        $filePath = storage_path('app/imports/' . $filename);
        $archivePath = storage_path('app/imports/archives');
        
        if (!file_exists($filePath)) {
            return redirect()->route('admin.files')->with('error', 'File not found.');
        }

        try {
            // Ensure archive directory exists
            if (!is_dir($archivePath)) {
                mkdir($archivePath, 0755, true);
            }
            
            // Move file to archive instead of deleting
            $archiveFilePath = $archivePath . '/' . $filename;
            $counter = 1;
            $baseName = pathinfo($filename, PATHINFO_FILENAME);
            $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Handle duplicate names in archive
            while (file_exists($archiveFilePath)) {
                $archiveFilePath = $archivePath . '/' . $baseName . '_' . $counter . '.' . $fileExtension;
                $counter++;
            }
            
            rename($filePath, $archiveFilePath);
            return redirect()->route('admin.files')->with('success', 'File moved to archive successfully.');
        } catch (\Exception $e) {
            Log::error('File archiving failed: ' . $e->getMessage());
            return redirect()->route('admin.files')->with('error', 'Failed to archive file: ' . $e->getMessage());
        }
    }

    private function importAssessmentRow($data)
    {
        $data = array_combine(
            array_map(fn($k) => strtolower(trim($k)), array_keys($data)),
            array_values($data)
        );

        $getValue = fn($value) => (
            $value === null || $value === '' || 
            (is_string($value) && strtolower(trim($value)) === 'unavailable')
        ) ? null : trim($value);

        $name = $this->resolveName($data, $getValue);
        $age = $this->resolveAge($data, $getValue);
        $gender = $this->resolveGender($data, $getValue);
        $program = $this->resolveProgram($data, $getValue);
        $yearLevel = $this->resolveYearLevel($data, $getValue);
        $overallRisk = $this->resolveOverallRisk($data);
        $answers = $this->resolveAnswers($data);
        $scores = $this->resolveScores($data, $answers);

        $this->validateRequiredFields($age, $gender, $program, $yearLevel);

        Assessment::create([
            'name' => $name,
            'age' => $age,
            'sex' => $gender,
            'college' => $program,
            'year' => $yearLevel,
            'Burnout_Category' => $overallRisk,
            'Exhaustion' => $scores['exhaustion'],
            'Disengagement' => $scores['disengagement'],
            'answers' => json_encode($answers),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function resolveName($data, $getValue)
    {
        $firstName = $getValue($data['first_name'] ?? null);
        $lastName = $getValue($data['last_name'] ?? null);
        $name = $getValue($data['name'] ?? $data['full_name'] ?? null);
        
        if (empty($name) && (!empty($firstName) || !empty($lastName))) {
                $name = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));
        }
        
        if (empty($name)) {
            $lastAnon = Assessment::where('name', 'like', 'Anonymous%')->orderByDesc('id')->first();
            $anonNum = $lastAnon && preg_match('/Anonymous(\d+)/', $lastAnon->name, $m) 
                ? intval($m[1]) + 1 
                : 1;
            $name = 'Anonymous' . $anonNum;
        }

        return $name;
    }

    private function resolveAge($data, $getValue)
    {
        $ageValue = $getValue($data['age'] ?? null);
        return !empty($ageValue) && is_numeric($ageValue) ? (int)$ageValue : null;
    }
        
    private function resolveGender($data, $getValue)
    {
        $gender = $getValue($data['sex'] ?? $data['gender'] ?? null);
        return empty($gender) ? 'unavailable' : $gender;
        }
        
    private function resolveProgram($data, $getValue)
    {
        $program = $getValue($data['college'] ?? $data['program'] ?? $data['department'] ?? null);
        return empty($program) ? 'unavailable' : $program;
        }
        
    private function resolveYearLevel($data, $getValue)
    {
        $yearLevel = $getValue($data['year'] ?? $data['year_level'] ?? $data['yearlevel'] ?? $data['grade'] ?? null);
        return empty($yearLevel) ? 'unavailable' : $yearLevel;
    }

    private function resolveOverallRisk($data)
    {
        $riskKeys = ['category', 'burnout_category', 'overall_risk', 'risk'];
        
        foreach ($riskKeys as $key) {
            if (!isset($data[$key])) continue;
            
            $value = $data[$key];
            if ($value === 'unavailable' || $value === '' || $value === null) {
                continue;
            }
            
            if (is_numeric($value)) {
                $categoryNum = (int)$value;
                if ($categoryNum >= 0 && $categoryNum <= 3) {
                    return (string)$categoryNum;
                }
                return null;
            }
            
            $risk = strtolower(trim($value));
            $categoryMap = [
                'low' => '0',
                'non-burnout' => '0',
                'low burnout' => '0',
                'disengaged' => '1',
                'exhausted' => '2',
                'high' => '3',
                'burnout' => '3',
                'high burnout' => '3'
            ];
            
            if (isset($categoryMap[$risk])) {
                return $categoryMap[$risk];
            }
        }
        
        return null;
    }

    private function resolveAnswers($data)
    {
        $answers = [];
        for ($i = 1; $i <= 30; $i++) {
            $qKey = 'q' . $i;
            if (isset($data[$qKey])) {
                $answerValue = $data[$qKey];
                $answers[] = ($answerValue === 'unavailable' || $answerValue === '' || $answerValue === null || !is_numeric($answerValue))
                    ? null
                    : (int)$answerValue;
            } else {
                $answers[] = null;
            }
        }
        
        if (empty(array_filter($answers)) && isset($data['answers'])) {
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

        return $answers;
    }

    private function resolveScores($data, $answers)
    {
        $exhaustionScore = $this->extractScore($data, ['exhaustion', 'exhaustion_score']);
        $disengagementScore = $this->extractScore($data, ['disengagement', 'disengagement_score']);
        
        if ($exhaustionScore === null && $disengagementScore === null && isset($data['olbi_score']) && $data['olbi_score'] !== 'unavailable') {
            $olbiScore = (int)$data['olbi_score'];
            $exhaustionScore = round($olbiScore / 2);
            $disengagementScore = $olbiScore - $exhaustionScore;
        }
        
        $hasAnswers = (bool)array_filter($answers);
        if (($exhaustionScore === null || $disengagementScore === null) && $hasAnswers) {
            $exhaustionItems = [15, 16, 19, 20, 22, 24, 27, 28];
            $disengagementItems = [14, 17, 18, 21, 23, 25, 26, 29];
            
            if ($exhaustionScore === null) {
                $exhaustionScore = $this->calculateScore($answers, $exhaustionItems);
            }
            
            if ($disengagementScore === null) {
                $disengagementScore = $this->calculateScore($answers, $disengagementItems);
            }
        }
        
        return ['exhaustion' => $exhaustionScore, 'disengagement' => $disengagementScore];
    }

    private function extractScore($data, $keys)
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== 'unavailable') {
                if ($key === 'exhaustion' || $key === 'disengagement') {
                    return (int)round((float)$data[$key] * 8);
                }
                return (int)$data[$key];
            }
        }
        return null;
    }

    private function calculateScore($answers, $indices)
    {
        $score = 0;
        foreach ($indices as $idx) {
            if (isset($answers[$idx]) && $answers[$idx] !== null && is_numeric($answers[$idx])) {
                $score += (int)$answers[$idx];
            }
        }
        return $score > 0 ? $score : null;
    }

    private function validateRequiredFields($age, $gender, $program, $yearLevel)
    {
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
    }
}

