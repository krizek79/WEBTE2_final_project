<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


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
     */
    public function updateFileSetting(Request $request)
    {
        $fileDataArray = $request->all();

        $updatedFiles = [];
        
        foreach ($fileDataArray as $fileData) {
            $validatedData = Validator::make($fileData, [
                'file_id' => 'required|integer',
                'points' => 'required|integer',
                'accessible_from' => 'date|nullable',
                'accessible_to' => 'date|nullable',
            ])->validate();
            
            $file = File::find($validatedData['file_id']);

            if (!$file) {
                throw new CustomException("No file found with the specified ID", 404);
            }

            $file->points = $validatedData['points'];
            $file->is_accessible = true;
            $file->accessible_from = $validatedData['accessible_from'];
            $file->accessible_to = $validatedData['accessible_to'];

            $file->save();

            array_push($updatedFiles, $file);
        }

        return $updatedFiles;
    }



    /**
     * @throws CustomException
     */
    public function updateFilePoints(Request $request)
    {
        $validatedData = $request->validate([
            'file_id' => 'required|integer',
            'points' => 'required|integer'
        ]);
        
        $file = File::where('id', $validatedData['file_id'])->first();

        if (!$file) {
            throw new CustomException("No files found", 404);
        }

        $file->points = $validatedData['points'];
        $file->save();

        return $file;
    }

    /**
     * @throws CustomException
     */
    public function updateAccessibility(Request $request)
    {
        $validatedData = $request->validate([
            'file_id' => 'required|integer',
            'is_accessible' => 'required|boolean',
        ]);

        $file = File::where('id', $validatedData['file_id'])->first();

        if (!$file) {
            throw new CustomException("No file found with the specified file name", 404);
        }

        $file->is_accessible = $request->is_accessible;

        $file->save();

        return $file;
    }

    /**
     * @throws CustomException
     */
    public function updateAccessibilityTime(Request $request)
    {
        $validatedData = $request->validate([
            'file_id' => 'required|integer',
            'accessible_from' => 'date|nullable',
            'accessible_to' => 'date|nullable',
        ]);

        $file = File::where('id', $validatedData['file_id'])->first();

        if (!$file) {
            throw new CustomException("File not found", 404);
        }

        $file->accessible_from = $request->get('accessible_from');
        $file->accessible_to = $request->get('accessible_to');
        $file->save();

        return $file;
    }
}
