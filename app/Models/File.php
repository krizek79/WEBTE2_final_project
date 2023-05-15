<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'file_name',
        'points',
        'is_accessible',
        'accessible_from',
        'accessible_to'
    ];

    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'points' => 'integer',
    ];

    /**
     * Get the tasks for the file.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'file_id');
    }
}
