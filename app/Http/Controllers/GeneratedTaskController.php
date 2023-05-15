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

        $generatedTasks = GeneratedTask::where('student_id', 1/*$student->id*/)
                                ->with(['task.file'])
                                ->get();

        if ($generatedTasks->isEmpty()) {
            return response()->json(['error' => 'No generated tasks found for this student'], 404);
        }

        return response()->json($generatedTasks->map(function ($generatedTask) {
            return [
                'id' => $generatedTask->id,
                'task_id' => $generatedTask->task->id,
                'task' => $generatedTask->task->task,
                'solution' => $generatedTask->task->solution,
                'student_answer' => $generatedTask->student_answer,
                'correctness' => $generatedTask->correctness,
                'points' => $generatedTask->task->file->points, 
                'file_name' => $generatedTask->task->file->file_name,
                /*'task' => [
                    'id' => $generatedTask->task->id,
                    'task' => $generatedTask->task->task,
                    'solution' => $generatedTask->task->solution,
                    'image' => $generatedTask->task->image,
                    'points' => $generatedTask->task->points,
                    'file' => [
                        'id' => $generatedTask->task->file->id,
                        'file_name' => $generatedTask->task->file->file_name,
                        'is_accessible' => $generatedTask->task->file->is_accessible,
                        'accessible_from' => $generatedTask->task->file->accessible_from,
                        'accessible_to' => $generatedTask->task->file->accessible_to,
                    ],
                ],*/
            ];
        }));
    }

}
