<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Assignment;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = ['file', 'score', 'user_comment', 'teacher_comment', 'user_id', 'assignment_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function assignment(){
        return $this->belongsTo(Assignment::class);
    }
}
