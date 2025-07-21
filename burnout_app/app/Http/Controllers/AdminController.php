<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalAssessments = Assessment::count();
        $highRisk = Assessment::where('overall_risk', 'high')->count();
        $moderateRisk = Assessment::where('overall_risk', 'moderate')->count();
        $lowRisk = Assessment::where('overall_risk', 'low')->count();

        $recentAssessments = Assessment::with('user')
            ->latest()
            ->take(10)
            ->get();

        $riskDistribution = [
            'low' => $lowRisk,
            'moderate' => $moderateRisk,
            'high' => $highRisk
        ];

        $monthlyTrends = Assessment::selectRaw('
            strftime("%m", created_at) as month,
            strftime("%Y", created_at) as year,
            overall_risk,
            COUNT(*) as count
        ')
        ->where('created_at', '>=', Carbon::now()->subMonths(6))
        ->groupBy('month', 'year', 'overall_risk')
        ->orderBy('year')
        ->orderBy('month')
        ->get()
        ->groupBy(['month', 'overall_risk']);

        return view('admin.dashboard', compact(
            'totalAssessments',
            'highRisk',
            'moderateRisk',
            'lowRisk',
            'recentAssessments',
            'riskDistribution',
            'monthlyTrends'
        ));
    }

    public function students()
    {
        $students = Assessment::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.students', compact('students'));
    }

    public function reports()
    {
        return view('admin.reports');
    }
}