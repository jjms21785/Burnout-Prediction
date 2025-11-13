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
        'ip_address',
        'user_agent',
        'confidence',
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
}
