<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User_in_course;
use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Post;
use App\Models\Board;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'access_code', 'user_id', 'img_id'];

    public function userInCourses(){
        return $this->hasMany(User_in_course::class);
    }

    public function submissions(){
        return $this->hasMany(Submission::class);
    }

    public function assignments(){
        return $this->hasMany(Assignment::class);
    }

    public function posts(){
        return $this->hasMany(Post::class);
    }

}
