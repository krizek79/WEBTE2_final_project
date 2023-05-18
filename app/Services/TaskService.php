<?php

namespace App\Services;

use Exception;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Validator;
use App\Models\Task;
use App\Models\File;
use App\Models\GeneratedTask;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskService
{

    /**
     * @throws CustomException
     */
    public function getAllTasks()
    {
        try {
            $tasks = Task::with('file')->get();

            if ($tasks->isEmpty()) {
                return [];
            }

            return $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'task' => $task->task,
                    'solution' => $task->solution,
                    'image' => $task->image,
                    'points' => $task->file->points,
                    'file_name' => $task->file->file_name
                ];
            });
        } catch (Exception $e) {
            throw new CustomException("An error occurred while trying to retrieve all tasks: " . $e->getMessage(), 500);
        }
    }

    /**
     * @param int $taskId
     * @return array
     * @throws CustomException
     */
    public function getTaskById($taskId)
    {
        try {
            $task = Task::with('file')->find($taskId);

            if (!$task) {
                throw new CustomException('Task not found for id ' . $taskId, 404);
            }

            return [
                'id' => $task->id,
                'task' => $task->task,
                'image' => $task->image,
                'points' => $task->file->points,
                'file_name' => $task->file->file_name
            ];
        } catch (CustomException $e) {
            throw $e; 
        } catch (Exception $e) {
            throw new CustomException("An error occurred while trying to retrieve task: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws CustomException
     */
    public function generateTasks(Request $request)
    {
        try{
            $now = Carbon::now();

            // Get the data from the request
            $requestData = $request->all();

            // Array to store all tasks
            $allTasks = collect();

            foreach ($requestData as $data) {
                $validator = Validator::make($data, [
                    'id' => 'required|integer',
                    'tasksCount' => 'required|integer|min:1',
                ]);

                if ($validator->fails()) {
                    $errorMessage = $validator->errors()->first();
                    throw new CustomException("Validation error: " . $errorMessage, 400);
                }

                $fileId = $data['id'];
                $numTasks = $data['tasksCount'];

                // Check if the file exists in the database
                $file = File::find($fileId);
                if (!$file) {
                    throw new CustomException("File with id " . $fileId . " does not exist", 400);
                }

                // Fetch the requested number of random tasks associated with the given file
                $tasksQuery = Task::whereHas('file', function($query) use ($now, $fileId) {
                    $query->where('file_id', $fileId)
                        ->where('is_accessible', true)
                        ->where(function($query) use ($now) {
                            $query->where(function($query) use ($now) {
                                $query->where('accessible_from', '<=', $now)
                                    ->where('accessible_to', '>=', $now);
                            })
                            ->orWhereNull('accessible_from')
                            ->orWhereNull('accessible_to');
                        });
                });

                $studentId = $request->user()->id;

                $generatedTaskIds = GeneratedTask::where('student_id', $studentId)->pluck('task_id')->toArray();

                $tasksQuery = $tasksQuery->whereNotIn('id', $generatedTaskIds);

                $tasks = $tasksQuery->inRandomOrder()
                    ->take($numTasks)
                    ->get();

                // Create a new GeneratedTask for each task and associate it with the current student
                foreach ($tasks as $task) {
                    $generatedTask = new GeneratedTask;
                    $generatedTask->student_id = $studentId;
                    $generatedTask->task_id = $task->id;
                    $generatedTask->correctness = 'NOT_EVALUATED';
                    $generatedTask->save();
                }

                // Append the tasks to the allTasks collection
                $allTasks = $allTasks->concat($tasks);
            }

            if ($allTasks->isEmpty()) {
                throw new CustomException("No accessible tasks found in all requested files", 404);
            }

            return ($allTasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'task' => $task->task,
                    'image' => $task->image,
                    'points' => $task->file->points,
                    'file_name' => $task->file->file_name
                ];
            }));
        } catch (CustomException $e) {
            throw $e; 
        } catch (Exception $e) {
            throw new CustomException("An error occurred while trying to generate tasks: " . $e->getMessage(), 500);
        }
    }
}
