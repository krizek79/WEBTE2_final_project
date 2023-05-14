<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GeneratedTask;

class GeneratedTaskController extends Controller
{
    public function getTasksByStudent()
    {
        $student = Auth::user();

        $generatedTasks = GeneratedTask::where('student_id', $student->id)
                                 ->with('task')
                                 ->get();


        if ($generatedTasks->isEmpty()) {
            return response()->json(['error' => 'No generated tasks found for this student'], 404);
        }

        // Return the generated tasks
        return response()->json($generatedTasks);
    }
}
