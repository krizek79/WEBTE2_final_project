<?php

namespace App\Services;

use App\Exceptions\CustomException;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\File;
use App\Models\GeneratedTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeneratedTaskService
{

    /**
     * @throws CustomException
     */
    public function getTasksByStudent($id)
    {
        $generatedTasks = [];

        try {
            $generatedTasks = GeneratedTask::where('student_id', $id)
                ->with(['task.file'])
                ->get();
        } catch (Exception $e) {
            return $generatedTasks;
        }

        return ($generatedTasks->map(function ($generatedTask) {
            return [
                //'id' => $generatedTask->id,
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
    public function getTaskListByStudent($request)
    {
        $studentId = $request->user()->id;
        $generatedTasks = [];

        try {
            $generatedTasks = GeneratedTask::where('student_id', $studentId)
                ->with(['task.file'])
                ->get();
        } catch (Exception $e) {
            return $generatedTasks;
        }

        return ($generatedTasks->map(function ($generatedTask) {
            return [
                'id' => $generatedTask->task_id,
                'task' => $generatedTask->task->task,
                'file_name' => $generatedTask->task->file->file_name
            ];
        }));
    }

    /**
     * @throws CustomException
     */
    public function updateStudentAnswer(Request $request, $id)
    {
        //$studentId = 1; //$request->user();
        $studentId = $request->user()->id;

        $request->validate([
            'student_answer' => 'required|string',
        ]);

        $generatedTask = GeneratedTask::where('student_id', $studentId)
                                    ->where('task_id', $id)
                                    ->first();

        if (!$generatedTask) {
            throw new CustomException("No generated task found for the current student and specified task", 404);
        }

        $generatedTask->student_answer = $request->student_answer;

        $generatedTask->correctness = "CORRECT";//$this->compareStudentAnswer($generatedTask->task->solution, $request->student_answer);

        $generatedTask->save();

        $results[] = [
            'student_id' => $studentId,
            'task_id' => $id, // Assuming the student model has a name field
            'student_answer' => $request->student_answer,
            'correctness' => $generatedTask->correctness,
        ];

        return $generatedTask;
    }

    /**
     * @throws CustomException
     */
    public function getStudentsResults(): array
    {
        $results = [];

        $students = GeneratedTask::select('student_id')
            ->with('student', 'task.file')
            ->groupBy('student_id')
            ->get();

        foreach ($students as $student) {
            $tasks = $student->student->generatedTasks;

            $totalTasks = $tasks->count();

            $solvedTasks = $tasks->whereNotNull('student_answer')->count();

            $totalPoints = $tasks->where('correctness', 'CORRECT')->sum(function ($task) {
                return $task->task->file->points;
            });

            $results[] = [
                'studentId' => $student->student_id,
                'firstName' => $student->student->first_name, // Assuming the student model has a name field
                'lastName' => $student->student->last_name,
                'totalTasks' => $totalTasks,
                'solvedTasks' => $solvedTasks,
                'totalPoints' => $totalPoints,
            ];
        }

        return $results;
    }

    private function compareStudentAnswer($solution, $studentAnswer): string
    {

        if ($solution === $studentAnswer) {
            return 'CORRECT';
        }

        return 'WRONG';
    }

}
