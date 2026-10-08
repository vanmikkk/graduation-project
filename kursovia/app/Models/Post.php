<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Course;
use App\Models\Comment;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['text', 'user_id', 'course_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }
}
