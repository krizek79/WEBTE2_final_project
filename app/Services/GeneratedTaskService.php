<?php

namespace App\Services;

use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use App\Models\File;
use App\Models\GeneratedTask;
use Illuminate\Http\Request;

class GeneratedTaskService
{

    /**
     * @throws CustomException
     */
    public function getFiles()
    {
        $files = File::select('id', 'file_name')->distinct()->get();

        if ($files->isEmpty()) {
            throw new CustomException("No files found", 404);
        }
        return $files;
    }

    /**
     * @throws CustomException
     */
    public function getTasksByStudent()
    {
        //$student = Auth::user();  

        $generatedTasks = GeneratedTask::where('student_id', 1 /*$student->id*/)
                                ->with(['task.file'])
                                ->get();

        if ($generatedTasks->isEmpty()) {
            throw new CustomException("No generated tasks found for this student", 404);
        }

        return ($generatedTasks->map(function ($generatedTask) {
            return [
                'id' => $generatedTask->id,
                'task_id' => $generatedTask->task->id,
                'task' => $generatedTask->task->task,
                'solution' => $generatedTask->task->solution,
                'student_answer' => $generatedTask->student_answer,
                'correctness' => $generatedTask->correctness,
                'points' => $generatedTask->task->file->points, 
                'file_name' => $generatedTask->task->file->file_name,
            ];
        }));
    }

    /**
     * @throws CustomException
     */
    public function updateStudentAnswer(Request $request, $taskId)
    {
        //$student = $request->user();

        $request->validate([
            'student_answer' => 'required|string',
        ]);

        $generatedTask = GeneratedTask::where('student_id',1/*$student->id*/)
                                    ->where('task_id', $taskId)
                                    ->first();

        if (!$generatedTask) {
            throw new CustomException("No generated task found for the current student and specified task", 404);
        }

        $generatedTask->student_answer = $request->student_answer;

        $generatedTask->correctness = "CORRECT";//$this->compareStudentAnswer($generatedTask->task->solution, $request->student_answer);

        $generatedTask->save();

        return $generatedTask;
    }

    /**
     * @throws CustomException
     */
    public function getResults()
    {
        $students = GeneratedTask::select('student_id')
                        ->with('student', 'task.file')
                        ->groupBy('student_id')
                        ->get();

        $results = [];

        foreach ($students as $student) {
            $tasks = $student->student->generatedTasks;

            $totalTasks = $tasks->count();

            $solvedTasks = $tasks->whereNotNull('student_answer')->count();

            $totalPoints = $tasks->where('correctness', 'CORRECT')->sum(function ($task) {
                return $task->task->file->points;
            });

            $results[] = [
                'student_id' => $student->student_id,
                'first_name' => $student->student->first_name, // Assuming the student model has a name field
                'last_name' => $student->student->first_name,
                'total_tasks' => $totalTasks,
                'solved_tasks' => $solvedTasks,
                'total_points' => $totalPoints,
            ];
        }

        return $results;

    }

    private function compareStudentAnswer($solution, $studentAnswer)
    {

        if ($solution === $studentAnswer) {
            return 'CORRECT';
        }

        return 'WRONG';
    }
    
}
