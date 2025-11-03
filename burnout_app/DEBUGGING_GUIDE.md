# Debugging Guide for Dashboard and Data Import Issues

## Overview
This guide helps you debug issues where imported data or new assessment submissions don't appear correctly on the dashboard but work on the report page.

## What Was Fixed

### 1. File Import (FileController.php)
- **Issue**: Imported files might not have `Exhaustion` and `Disengagement` scores calculated if they weren't in the CSV.
- **Fix**: Added automatic calculation from Q1-Q30 answers if scores are missing during import.
- **Location**: `app/Http/Controllers/FileController.php` - `importAssessmentRow()` method

### 2. Dashboard (AdminController.php)
- **Issue**: Dashboard filtered out records that didn't have both `Exhaustion` AND `Disengagement` scores.
- **Fix**: 
  - Dashboard now processes ALL records (not just those with scores)
  - Automatically calculates scores from Q1-Q30 answers if missing
  - Shows "Unknown" category for records that can't be categorized
- **Location**: `app/Http/Controllers/AdminController.php` - `dashboard()` and `calculateBurnoutCategoryWithScores()` methods

### 3. Assessment Submissions (AssessmentController.php)
- **Issue**: If Flask API bar graph didn't have values, scores were saved as null.
- **Fix**: Always uses calculated scores as fallback if bar graph values are missing.
- **Location**: `app/Http/Controllers/AssessmentController.php` - `result()` method

## How to Debug

### Step 1: Check Database Records

Run these commands in Laravel Tinker (`php artisan tinker`):

```php
// Check total records
\App\Models\Assessment::count();

// Check records with scores
\App\Models\Assessment::whereNotNull('Exhaustion')->whereNotNull('Disengagement')->count();

// Check records without scores
\App\Models\Assessment::whereNull('Exhaustion')->orWhereNull('Disengagement')->count();

// Check a specific record
$assessment = \App\Models\Assessment::first();
$assessment->Exhaustion;
$assessment->Disengagement;
$assessment->raw_answers; // Should return array of Q1-Q30 answers
```

### Step 2: Check Data Format

Verify the data structure:

```php
$assessment = \App\Models\Assessment::latest()->first();

// Check if answers exist
$answers = $assessment->raw_answers;
var_dump(is_array($answers)); // Should be true
var_dump(count($answers)); // Should be 30

// Check column values
echo "Name: " . $assessment->name . "\n";
echo "Sex: " . $assessment->sex . "\n"; // Not gender!
echo "College: " . $assessment->college . "\n"; // Not program!
echo "Year: " . $assessment->year . "\n"; // Not year_level!
echo "Exhaustion: " . ($assessment->Exhaustion ?? 'NULL') . "\n";
echo "Disengagement: " . ($assessment->Disengagement ?? 'NULL') . "\n";
```

### Step 3: Test Score Calculation

Manually test if scores can be calculated:

```php
$assessment = \App\Models\Assessment::whereNull('Exhaustion')->orWhereNull('Disengagement')->first();
if ($assessment) {
    $answers = $assessment->raw_answers;
    
    // Exhaustion items: Q16, Q17, Q20, Q21, Q23, Q25, Q28, Q29
    // (indices 15, 16, 19, 20, 22, 24, 27, 28)
    $exhaustionItems = [15, 16, 19, 20, 22, 24, 27, 28];
    $exhaustion = 0;
    foreach ($exhaustionItems as $idx) {
        if (isset($answers[$idx])) {
            $exhaustion += $answers[$idx];
        }
    }
    echo "Calculated Exhaustion: $exhaustion\n";
    
    // Disengagement items: Q15, Q18, Q19, Q22, Q24, Q26, Q27, Q30
    // (indices 14, 17, 18, 21, 23, 25, 26, 29)
    $disengagementItems = [14, 17, 18, 21, 23, 25, 26, 29];
    $disengagement = 0;
    foreach ($disengagementItems as $idx) {
        if (isset($answers[$idx])) {
            $disengagement += $answers[$idx];
        }
    }
    echo "Calculated Disengagement: $disengagement\n";
}
```

### Step 4: Check Dashboard Query

Test the dashboard calculation directly:

```php
// Simulate dashboard calculation
$assessments = \App\Models\Assessment::all();
$lowBurnout = 0;
$disengagement = 0;
$exhaustion = 0;
$highBurnout = 0;

foreach ($assessments as $assessment) {
    $ex = $assessment->Exhaustion;
    $dis = $assessment->Disengagement;
    
    // Calculate from answers if missing
    if ($ex === null || $dis === null) {
        $answers = $assessment->raw_answers ?? [];
        if (is_array($answers) && count($answers) >= 30) {
            $exhaustionItems = [15, 16, 19, 20, 22, 24, 27, 28];
            $disengagementItems = [14, 17, 18, 21, 23, 25, 26, 29];
            
            if ($ex === null) {
                $ex = 0;
                foreach ($exhaustionItems as $idx) {
                    $ex += $answers[$idx] ?? 0;
                }
            }
            
            if ($dis === null) {
                $dis = 0;
                foreach ($disengagementItems as $idx) {
                    $dis += $answers[$idx] ?? 0;
                }
            }
        }
    }
    
    if ($ex !== null && $dis !== null) {
        $highEx = $ex >= 18;
        $highDis = $dis >= 17;
        
        if (!$highEx && !$highDis) {
            $lowBurnout++;
        } elseif (!$highEx && $highDis) {
            $disengagement++;
        } elseif ($highEx && !$highDis) {
            $exhaustion++;
        } else {
            $highBurnout++;
        }
    }
}

echo "Low Burnout: $lowBurnout\n";
echo "Disengaged: $disengagement\n";
echo "Exhausted: $exhaustion\n";
echo "High Burnout: $highBurnout\n";
```

### Step 5: Check Logs

View Laravel logs for errors:

```bash
tail -f storage/logs/laravel.log
```

Look for:
- Database query errors
- JSON parsing errors
- Missing column errors

### Step 6: Verify Import Process

When importing a file, check:

1. **File Format**: Ensure CSV has columns: `first_name`, `last_name`, `sex`, `age`, `year`, `college`, `Q1` through `Q30`, `Exhaustion`, `Disengagement`, `Category`

2. **Import Logs**: Check if import was successful:
```php
// In tinker after import
\App\Models\Assessment::latest()->first(); // Should show the imported record
```

3. **Score Calculation**: Verify scores were calculated or imported:
```php
$latest = \App\Models\Assessment::latest()->first();
if ($latest->Exhaustion === null || $latest->Disengagement === null) {
    echo "ERROR: Scores not calculated!\n";
    echo "Has answers: " . (count($latest->raw_answers ?? []) === 30 ? 'YES' : 'NO') . "\n";
}
```

## Common Issues and Solutions

### Issue: Dashboard shows 0 for all categories
**Solution**: 
1. Check if records exist: `Assessment::count()`
2. Check if records have scores or answers
3. Verify the calculation logic is working (use Step 4)

### Issue: Imported data doesn't appear on dashboard
**Solution**:
1. Check column names match (use `sex`, `college`, `year`, not `gender`, `program`, `year_level`)
2. Verify Q1-Q30 answers are imported correctly
3. Check if scores are being calculated during import

### Issue: New assessments don't show up
**Solution**:
1. Verify AssessmentController is saving `Exhaustion` and `Disengagement`
2. Check if answers are being saved correctly
3. Verify the database columns exist and are correct

### Issue: Dashboard and Report show different counts
**Solution**:
1. Report page doesn't filter by scores - it shows all records
2. Dashboard now also shows all records, but categorizes them
3. If counts differ, check if some records are "Unknown" category on dashboard

## Database Column Mapping

**Important**: The database uses these column names:
- `sex` (not `gender`)
- `college` (not `program`)
- `year` (not `year_level`)
- `Exhaustion` (not `exhaustion_score`)
- `Disengagement` (not `disengagement_score`)
- `Burnout_Category` (not `overall_risk`)

The model has accessors that map old names to new names for backward compatibility.

## Testing Import

Create a test CSV file:

```csv
first_name,last_name,sex,age,year,college,Q1,Q2,Q3,Q4,Q5,Q6,Q7,Q8,Q9,Q10,Q11,Q12,Q13,Q14,Q15,Q16,Q17,Q18,Q19,Q20,Q21,Q22,Q23,Q24,Q25,Q26,Q27,Q28,Q29,Q30
John,Doe,Male,20,1st Year,College of Computer Studies,1,2,3,4,5,1,2,3,4,5,1,2,3,4,5,1,2,3,4,5,1,2,3,4,5
```

Import it and verify:
```php
$assessment = \App\Models\Assessment::where('name', 'John Doe')->first();
$assessment->Exhaustion; // Should be calculated
$assessment->Disengagement; // Should be calculated
```

## Quick Health Check

Run this in tinker to check everything:

```php
$total = \App\Models\Assessment::count();
$withScores = \App\Models\Assessment::whereNotNull('Exhaustion')->whereNotNull('Disengagement')->count();
$withAnswers = \App\Models\Assessment::whereNotNull('answers')->count();

echo "Total Records: $total\n";
echo "Records with Scores: $withScores\n";
echo "Records with Answers: $withAnswers\n";
echo "Records needing calculation: " . ($total - $withScores) . "\n";

// Test latest record
$latest = \App\Models\Assessment::latest()->first();
if ($latest) {
    echo "\nLatest Record:\n";
    echo "  Name: {$latest->name}\n";
    echo "  Exhaustion: " . ($latest->Exhaustion ?? 'NULL') . "\n";
    echo "  Disengagement: " . ($latest->Disengagement ?? 'NULL') . "\n";
    echo "  Has Answers: " . (count($latest->raw_answers ?? []) >= 30 ? 'YES' : 'NO') . "\n";
}
```

If everything is working, you should see:
- Total Records > 0
- Records with Scores should match or be close to Total Records
- Latest record should have scores or answers

