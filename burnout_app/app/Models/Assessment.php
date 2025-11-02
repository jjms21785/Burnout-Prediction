<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        // Demographic fields (database columns)
        'sex',
        'age',
        'year',
        'college',
        // Question answers - can be stored as JSON 'answers' column or individual Q1-Q30 columns
        'answers',
        // For individual question columns, add them dynamically or handle in accessors
        // Calculated scores (database column names)
        'Exhaustion',
        'Disengagement',
        'Burnout_Category',
        // Additional fields for tracking
        'ip_address',
        'user_agent',
        'name', // For backward compatibility
        // Legacy field names for backward compatibility (if database still has them)
        'gender', 'program', 'year_level',
        'exhaustion_score', 'disengagement_score', 'overall_risk',
        'confidence',
    ];
    
    // Note: $casts removed per user request
    // If 'answers' is stored as JSON, you may need to handle encoding/decoding manually
    // or add back: protected $casts = ['answers' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getUniquePrograms()
    {
        // Use college column (preferred) or fallback to program for backward compatibility
        return self::query()->distinct()
            ->selectRaw('COALESCE(college, program) as college')
            ->whereNotNull('college')
            ->orWhereNotNull('program')
            ->pluck('college')
            ->filter()
            ->values();
    }

    /**
     * Get the raw answers array (for backward compatibility)
     * Returns the responses array if in new format, or the answers directly if in old format
     * Handles both JSON string (when $casts removed) and array formats
     */
    public function getRawAnswersAttribute()
    {
        $answers = $this->answers;
        
        // Decode JSON string if needed (when $casts is removed)
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }
        
        // If answers is in new format with 'responses' key
        if (is_array($answers) && isset($answers['responses'])) {
            return $answers['responses'];
        }
        
        // If it's already a flat array (old format)
        return is_array($answers) ? $answers : [];
    }

    /**
     * Get interpretations if available
     * Handles both JSON string (when $casts removed) and array formats
     */
    public function getInterpretationsAttribute()
    {
        $answers = $this->answers;
        
        // Decode JSON string if needed (when $casts is removed)
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }
        
        if (is_array($answers) && isset($answers['interpretations'])) {
            return $answers['interpretations'];
        }
        
        return null;
    }

    /**
     * Get recommendations if available
     * Handles both JSON string (when $casts removed) and array formats
     */
    public function getRecommendationsAttribute()
    {
        $answers = $this->answers;
        
        // Decode JSON string if needed (when $casts is removed)
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }
        
        if (is_array($answers) && isset($answers['recommendations'])) {
            return $answers['recommendations'];
        }
        
        return null;
    }

    /**
     * Accessor for backward compatibility: gender -> sex
     */
    public function getGenderAttribute()
    {
        return $this->sex;
    }

    /**
     * Accessor for backward compatibility: program -> college
     */
    public function getProgramAttribute()
    {
        return $this->college;
    }

    /**
     * Accessor for backward compatibility: year_level -> year
     */
    public function getYearLevelAttribute()
    {
        return $this->year;
    }

    /**
     * Accessor for backward compatibility: overall_risk -> Burnout_Category
     */
    public function getOverallRiskAttribute()
    {
        return $this->Burnout_Category;
    }

    /**
     * Accessor for backward compatibility: exhaustion_score -> Exhaustion
     */
    public function getExhaustionScoreAttribute()
    {
        return $this->Exhaustion;
    }

    /**
     * Accessor for backward compatibility: disengagement_score -> Disengagement
     */
    public function getDisengagementScoreAttribute()
    {
        return $this->Disengagement;
    }
}