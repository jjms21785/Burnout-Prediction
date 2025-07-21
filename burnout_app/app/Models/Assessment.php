<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'answers',
        'overall_risk',
        'ip_address',
        'user_agent',
        'confidence',
        'exhaustion_score',
        'disengagement_score',
        'name',
        'age',
        'gender',
        'program',
        'year_level',
        'student_id'
    ];

    protected $casts = [
        'answers' => 'array',
        'confidence' => 'float',
        'exhaustion_score' => 'integer',
        'disengagement_score' => 'integer',
        'age' => 'integer',
    ];

    public function getRiskBadgeColorAttribute()
    {
        return match($this->overall_risk) {
            'high' => 'bg-red-100 text-red-800',
            'moderate' => 'bg-orange-100 text-orange-800',
            'low' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getFormattedRiskAttribute()
    {
        return ucfirst($this->overall_risk) . ' Risk';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}