<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'age',
        'sex',
        'college',
        'year',
        'answers',
        'Exhaustion',
        'Disengagement',
        'Burnout_Category',
        'status',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getUniquePrograms()
    {
        return self::query()->distinct()
            ->select('college')
            ->whereNotNull('college')
            ->pluck('college')
            ->filter()
            ->values();
    }

    public function getRawAnswersAttribute()
    {
        $answers = $this->answers;
        
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }
        
        if (is_array($answers) && isset($answers['responses'])) {
            return $answers['responses'];
        }
        
        return is_array($answers) ? $answers : [];
    }

    public function getInterpretationsAttribute()
    {
        $answers = $this->answers;
        
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }
        
        if (is_array($answers) && isset($answers['interpretations'])) {
            return $answers['interpretations'];
        }
        
        return null;
    }

    public function getRecommendationsAttribute()
    {
        $answers = $this->answers;
        
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }
        
        if (is_array($answers) && isset($answers['recommendations'])) {
            return $answers['recommendations'];
        }
        
        return null;
    }

    /**
     * Get burnout category label from stored ML prediction
     * Converts numeric prediction (0,1,2,3) to human-readable label
     * 
     * @return string Category label: 'Low Burnout', 'Disengaged', 'Exhausted', 'High Burnout', or 'Unavailable'
     */
    public function getBurnoutCategoryLabel()
    {
        $category = $this->Burnout_Category;
        
        if ($category === null || $category === '' || $category === 'unavailable') {
            return 'Unavailable';
        }
        
        // If it's a numeric value (0, 1, 2, 3) - matches ML model prediction
        if (is_numeric($category)) {
            $categoryNum = (int)$category;
            switch ($categoryNum) {
                case 0:
                    return 'Low Burnout';  // ML: "Non-Burnout"
                case 1:
                    return 'Exhausted';    // ML: "Exhausted" (matches ML model)
                case 2:
                    return 'Disengaged';   // ML: "Disengaged" (matches ML model)
                case 3:
                    return 'High Burnout'; // ML: "BURNOUT"
                default:
                    return 'Unavailable';
            }
        }
        
        // If it's already a label, return as is (for backward compatibility)
        $categoryLower = strtolower(trim($category));
        $labelMap = [
            'low' => 'Low Burnout',
            'non-burnout' => 'Low Burnout',
            'disengaged' => 'Disengaged',
            'exhausted' => 'Exhausted',
            'high' => 'High Burnout',
            'burnout' => 'High Burnout'
        ];
        
        return $labelMap[$categoryLower] ?? $category;
    }

    /**
     * Get burnout category color class for display
     * 
     * @return string Tailwind CSS color class
     */
    public function getBurnoutCategoryColor()
    {
        $label = $this->getBurnoutCategoryLabel();
        
        switch ($label) {
            case 'Low Burnout':
                return 'bg-green-100 text-green-800';
            case 'Disengaged':
            case 'Exhausted':
                return 'bg-orange-100 text-orange-800';
            case 'High Burnout':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
    /**
     * Accessor for program (maps to college column)
     * Allows accessing college as ->program
     */
    public function getProgramAttribute()
    {
        return $this->college;
    }

    /**
     * Accessor for year_level (maps to year column)
     * Allows accessing year as ->year_level
     */
    public function getYearLevelAttribute()
    {
        return $this->year;
    }
}
