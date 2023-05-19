<?php

namespace App\Services;

use Exception;
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
        try{
            $files = File::select('id', 'file_name', 'points')->distinct()->get();

            if ($files->isEmpty()) {
                throw new CustomException("No files found", 404);
            }

            return $files;
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new CustomException("An error occurred while trying to retrieve all files: " . $e->getMessage(), 500);
        }
    }

    /**
     * @throws CustomException
     */
    public function getAccessibleFiles()
    {
        try{
            $files = File::where('is_accessible', true)
                ->select('id', 'file_name')
                ->withCount('tasks')  // Add a tasks_count column to the results
                ->get();

            if ($files->isEmpty()) {
                throw new CustomException("No accessible files found", 404);
            }

            return $files;

        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new CustomException("An error occurred while trying to retrieve accessible files: " . $e->getMessage(), 500);
        }

    }

    /**
     * @throws CustomException
     * @throws ValidationException
     */
    public function updateFileSetting(Request $request): array
    {
        try{
            $fileDataArray = $request->all();

            $updatedFiles = [];

            foreach ($fileDataArray as $fileData) {
                $validator = Validator::make($fileData, [
                    'id' => 'required|integer',
                    'points' => 'required|integer',
                    'accessibleFrom' => 'date|nullable',
                    'accessibleTo' => 'date|nullable',
                ]);

                if($validator->fails()) {
                    $errorMessage = $validator->errors()->first();
                    throw new CustomException("Validation error: " . $errorMessage, 400);
                }

                $validatedData = $validator->validated();

                $file = File::find($validatedData['id']);

                if (!$file) {
                    throw new CustomException("No file found with the specified id: " . $validatedData['id'], 404);
                }

                $file->points = $validatedData['points'];
                $file->is_accessible = true;
                if(isset($validatedData['accessibleFrom']) && !is_null($validatedData['accessibleFrom'])) {
                    $file->accessible_from = $validatedData['accessibleFrom'];
                }else{
                    $file->accessible_from = null;
                }

                if(isset($validatedData['accessibleTo']) && !is_null($validatedData['accessibleTo'])){
                    $file->accessible_to = $validatedData['accessibleTo'];
                }else{
                    $file->accessible_to = null;
                }

                $file->save();

                $updatedFiles[] = $file;
            }

            return $updatedFiles;

        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new CustomException("An error occurred while trying to update file settings: " . $e->getMessage(), 500);
        }
    }
}
