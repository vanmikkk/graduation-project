<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\User_in_course;
use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Post;
use App\Models\Token;
use App\Models\Comment;
use App\Models\Board;
use App\Models\Card;

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
        'FIO',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function userInCourses(){
        return $this->hasMany(User_in_course::class);
    }

    public function submissions(){
        return $this->hasMany(Submission::class);
    }

    public function assignments(){
        return $this->hasMany(Assignment::class);
    }

    public function tokens(){
        return $this->hasMany(Token::class);
    }

    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function boards(){
        return $this->hasMany(Board::class);
    }

    public function cards(){
        return $this->hasMany(Card::class);
    }
}
