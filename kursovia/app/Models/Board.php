<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Column;
use App\Models\User;
use App\Models\Course;

class Board extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'config'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function columns(){
        return $this->hasMany(Column::class);
    }
}
