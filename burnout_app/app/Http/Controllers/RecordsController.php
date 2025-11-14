<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\Log;

class RecordsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = Assessment::query();

            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('college', 'like', "%$search%")
                      ->orWhere('year', 'like', "%$search%")
                      ->orWhere('sex', 'like', "%$search%")
                      ->orWhere('age', 'like', "%$search%")
                      ->orWhere('Burnout_Category', 'like', "%$search%");
                });
            }

            if ($grade = $request->input('grade')) {
                $query->where('year', $grade);
            }
            if ($age = $request->input('age')) {
                $query->where('age', $age);
            }
            if ($gender = $request->input('gender')) {
                $query->where('sex', $gender);
            }
            if ($dept = $request->input('program')) {
                $query->where('college', $dept);
            }
            if ($risk = $request->input('risk')) {
                $query->where('Burnout_Category', strtolower($risk));
            }
            if ($time = $request->input('time')) {
                if ($time === '7days') {
                    $query->where('created_at', '>=', now()->subDays(7));
                } elseif ($time === 'month') {
                    $query->whereMonth('created_at', now()->month);
                }
            }
            $query->orderByDesc('created_at');

            $assessments = $query->get();

            $data = $assessments->map(function($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->name ?? 'Unavailable',
                    'gender' => $a->sex ?? 'Unavailable',
                    'age' => $a->age ?? 'Unavailable',
                    'program' => $a->college ?? 'Unavailable',
                    'grade' => $a->year ?? 'Unavailable',
                    'risk' => $a->Burnout_Category ?? 'Unavailable',
                    'last_update' => $a->updated_at ? $a->updated_at->format('M d') : 'Unavailable',
                ];
            });

            return response()->json($data);
        }
        
        return view('admin.records');
    }

    public function programs()
    {
        $programs = Assessment::getUniquePrograms();
        return response()->json($programs);
    }

    public function update(Request $request, $id)
    {
        try {
            $assessment = Assessment::findOrFail($id);
            
            $updateData = [
                'name' => $request->input('name', $assessment->name),
                'age' => $request->input('age', $assessment->age),
            ];
            
            if ($request->has('gender')) {
                $updateData['sex'] = $request->input('gender');
            } elseif ($assessment->sex) {
                $updateData['sex'] = $assessment->sex;
            }
            
            if ($request->has('program')) {
                $updateData['college'] = $request->input('program');
            } elseif ($assessment->college) {
                $updateData['college'] = $assessment->college;
            }
            
            if ($request->has('year_level')) {
                $updateData['year'] = $request->input('year_level');
            } elseif ($assessment->year) {
                $updateData['year'] = $assessment->year;
            }
            
            // Accept burnout_category (ML prediction value: 0,1,2,3)
            if ($request->has('burnout_category')) {
                $category = $request->input('burnout_category');
                // Validate it's a valid ML prediction value (0,1,2,3)
                if (in_array($category, ['0', '1', '2', '3', 0, 1, 2, 3])) {
                    $updateData['Burnout_Category'] = (string)$category;
                }
            } elseif ($assessment->Burnout_Category) {
                $updateData['Burnout_Category'] = $assessment->Burnout_Category;
            }
            
            $assessment->update($updateData);

            return response()->json(['success' => true, 'message' => 'Assessment updated successfully']);
        } catch (\Exception $e) {
            Log::error('Assessment update failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update assessment: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $assessment = Assessment::findOrFail($id);
            $assessment->delete();

            return response()->json(['success' => true, 'message' => 'Assessment deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Assessment deletion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete assessment: ' . $e->getMessage()], 500);
        }
    }
}

