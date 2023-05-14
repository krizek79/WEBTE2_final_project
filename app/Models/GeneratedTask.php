<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedTask extends Model
{
    use HasFactory;

    //public $timestamps = false;

    protected $fillable = [
        'student_id',
        'task_id', 
        'student_answer',
        'correctness'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
