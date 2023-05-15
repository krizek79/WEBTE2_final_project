<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\File;
use Illuminate\Support\Facades\File as Fs;

class TasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $folderPath = database_path('data');

        // Get all .tex files in the folder
        $files = Fs::files($folderPath, true);
        $texFiles = array_filter($files, function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'tex';
        });

        foreach ($texFiles as $file) {
            // Get the file name
            $fileName = pathinfo($file, PATHINFO_FILENAME);

            // Create file entry in 'files' table and get the id
            $fileEntry = File::create([
                'file_name' => $fileName,
                'points' => 1,
                'is_accessible' => true,
            ]);

            // Read the file
            $data = file_get_contents($file);

            // Use regex to extract tasks, solutions, and images
            preg_match_all('/\\\\begin\{task\}(.*?)\\\\end\{task\}/s', $data, $tasks);
            preg_match_all('/\\\\begin\{solution\}(.*?)\\\\end\{solution\}/s', $data, $solutions);
            preg_match_all('/\\\\includegraphics\{(.*?)\}/s', $data, $images);

            // Check if there are any images found
            if (!empty($images[1])) {
                // Combine tasks, solutions, and images in an array
                $tasks_solutions_images = array_map(function($task, $solution, $image){
                    return [
                        "task" => $task,
                        "solution" => $solution,
                        "image" => str_replace('zadanie99/', '', $image) // Remove "zadanie99/" from the image path
                    ];
                }, $tasks[1], $solutions[1], $images[1]);
            } else {
                // Set all rows to have the image field as NULL
                $tasks_solutions_images = array_map(function($task, $solution) {
                    return [
                        "task" => $task,
                        "solution" => $solution,
                        "image" => null
                    ];
                }, $tasks[1], $solutions[1]);
            }

            // Insert tasks, solutions, image paths, and file id into the database
            foreach ($tasks_solutions_images as $obj) {
                Task::create([
                    'task' => $obj['task'],
                    'solution' => $obj['solution'],
                    'image' => $obj['image'],
                    'file_id' => $fileEntry->id, // Save the file id in the database
                ]);
            }
        }
    }
}
