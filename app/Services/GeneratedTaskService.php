<?php

namespace App\Services;

use App\Exceptions\CustomException;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Models\File;
use App\Models\User;
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
        try {
            $generatedTasks = [];

            $generatedTasks = GeneratedTask::where('student_id', $id)
                ->with(['task.file'])
                ->get();


            if ($generatedTasks->isEmpty()) {
                return [];
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
        } catch (Exception $e) {
            throw new CustomException("An error occurred while trying to retrieve all student tasks: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws CustomException
     */
    public function getTaskListByStudent($request)
    {
        try {
            $studentId = $request->user()->id;
            $generatedTasks = [];

            $generatedTasks = GeneratedTask::where('student_id', $studentId)
                ->with(['task.file'])
                ->get();

            if ($generatedTasks->isEmpty()) {
                return [];
            }

            return ($generatedTasks->map(function ($generatedTask) {
                return [
                    'id' => $generatedTask->task_id,
                    'task' => $generatedTask->task->task,
                    'file_name' => $generatedTask->task->file->file_name
                ];
            }));

        } catch (Exception $e) {
            throw new CustomException("An error occurred while trying to retrieve tasks: " . $e->getMessage(), 500);
        }
    }
    

    /**
     * @throws CustomException
     */
    public function updateStudentAnswer(Request $request, $id)
    {
        try{
            $studentId = $request->user()->id;

            $validator = Validator::make($request->all(), [
                'student_answer' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->first();
                throw new CustomException("Validation error: " . $errorMessage, 400);
            }

            $generatedTask = GeneratedTask::where('student_id', $studentId)
                                        ->where('task_id', $id)
                                        ->first();

            if (!$generatedTask) {
                throw new CustomException("No task found for the current student and specified task", 404);
            }

            $generatedTask->student_answer = $request->student_answer;

            $generatedTask->correctness = $this->compareStudentAnswer($generatedTask->task->solution, $request->student_answer);

            $generatedTask->save();

            $results[] = [
                'student_id' => $studentId,
                'task_id' => $id,
                'student_answer' => $request->student_answer,
                'correctness' => $generatedTask->correctness,
            ];

            return $generatedTask;  //$results

        } catch (CustomException $e) {
            throw $e; 
        } catch (Exception $e) {
            throw new CustomException("An error occurred while trying to submit student answer: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws CustomException
     */
    public function getStudentsResults(): array
    {
        try{
            $results = [];

            $students = GeneratedTask::select('student_id')
                ->with('student', 'task.file')
                ->groupBy('student_id')
                ->get();

            if (!$students) {
                throw new CustomException("No students found.", 404);
            }

            foreach ($students as $student) {
                $tasks = $student->student->generatedTasks;

                $totalTasks = $tasks->count();

                $solvedTasks = $tasks->whereNotNull('student_answer')->count();

                $totalPoints = $tasks->where('correctness', 'CORRECT')->sum(function ($task) {
                    return $task->task->file->points;
                });

                $results[] = [
                    'studentId' => $student->student_id,
                    'firstName' => $student->student->first_name, 
                    'lastName' => $student->student->last_name,
                    'totalTasks' => $totalTasks,
                    'solvedTasks' => $solvedTasks,
                    'totalPoints' => $totalPoints,
                ];
            }

            return $results;

        } catch (CustomException $e) {
            throw $e; 
        } catch (Exception $e) {
            throw new CustomException("An error occurred while trying to retrieve student results: " . $e->getMessage(), 500);
        }
    }

    private function compareStudentAnswer($solution, $studentAnswer): string
    {
        $responses = ['CORRECT', 'WRONG'];
        $randomIndex = array_rand($responses);

        return $responses[$randomIndex];
    }


}
