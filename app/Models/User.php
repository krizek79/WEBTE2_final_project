<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    public static function create(array $data): User
    {
        $user = new self;
        $user->email = $data["email"];
        $user->password = bcrypt($data['password']);
        $user->role = $data['role'] ?? 'student';

        $user->save();
        return $user;
    }

    public function generatedTasks()
    {
        return $this->hasMany(GeneratedTask::class, 'student_id');
    }
}
