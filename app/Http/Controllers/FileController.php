<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;

class FileController extends Controller
{
    public function getFileNames()
{
    // Fetch all unique file_names from the files table
    $fileNames = File::distinct('file_name')->pluck('file_name');

    // Return the file names
    return response()->json($fileNames);
}

}
