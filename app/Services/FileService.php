<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class FileService
{

    /**
     * @throws CustomException
     */
    public function getFiles()
    {
        $files = File::select('id', 'file_name', 'points')->distinct()->get();

        if ($files->isEmpty()) {
            throw new CustomException("No files found", 404);
        }

        return $files;
    }

    /**
     * @throws CustomException
     */
    public function getAccessibleFiles()
    {
        $files = File::where('is_accessible', true)
            ->select('id', 'file_name')
            ->withCount('tasks')  // Add a tasks_count column to the results
            ->get();

        if ($files->isEmpty()) {
            throw new CustomException("No accessible files found", 404);
        }

        return $files;
    }

    /**
     * @throws CustomException
     * @throws ValidationException
     */
    public function updateFileSetting(Request $request): array
    {
        $fileDataArray = $request->all();

        $updatedFiles = [];

        foreach ($fileDataArray as $fileData) {
            $validatedData = Validator::make($fileData, [
                'id' => 'required|integer',
                'points' => 'required|integer',
                'accessibleFrom' => 'date|nullable',
                'accessibleTo' => 'date|nullable',
            ])->validate();

            $file = File::find($validatedData['id']);

            if (!$file) {
                throw new CustomException("No file found with the specified ID", 404);
            }

            $file->points = $validatedData['points'];
            $file->is_accessible = true;
            $file->accessible_from = $validatedData['accessibleFrom'];
            $file->accessible_to = $validatedData['accessibleTo'];

            $file->save();

            $updatedFiles[] = $file;
        }

        return $updatedFiles;
    }
}
